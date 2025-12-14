<?php

namespace App\Services\Fiscal;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Nfe;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use Illuminate\Support\Facades\Storage;
use Exception;

class NFeService
{
    public function generate(Company $company, Customer $customer, array $items): Nfe
    {
        // 1. Load Certificate
        $pfxContent = Storage::get($company->certificate->path);
        $password = $company->certificate->password; // Decrypted by model cast
        $certificate = Certificate::readPfx($pfxContent, $password);

        // 2. Init Tools
        $tools = new Tools(json_encode([
            "atualizacao" => "2023-01-01 00:00:00",
            "tpAmb" => 2, // Homologacao
            "razaosocial" => $company->razao_social,
            "cnpj" => $company->cnpj,
            "siglaUF" => $company->address->uf,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ]), $certificate);

        // 3. Create NFe Object
        $nfe = new Make();
        $std = new \stdClass();
        $std->versao = '4.00';
        $nfe->taginfNFe($std);

        $std = new \stdClass();
        $std->cUF = 35; // Example SP
        $std->natOp = 'VENDA';
        $std->mod = 55;
        $std->serie = 1;
        $std->nNF = rand(100, 9999); // Mock number
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = 1; // 1=Internal, 2=Interstate
        $std->cMunFG = 3550308; // SP
        $std->tpImp = 1;
        $std->tpEmis = 1; // 1=Normal
        $std->cDV = 0;
        $std->tpAmb = 2; // Homolog
        $std->finNFe = 1; // Normal
        $std->indFinal = 1;
        $std->indPres = 1;
        $std->procEmi = 0;
        $std->verProc = '1.0';
        $nfe->tagide($std);

        // Emitente
        $std = new \stdClass();
        $std->xNome = $company->razao_social;
        $std->CNPJ = $company->cnpj;
        $std->IE = $company->ie;
        $std->CRT = $company->regime_tributario;
        $nfe->tagemit($std);

        // Destinatario
        $std = new \stdClass();
        $std->xNome = $customer->razao_social;
        $std->CNPJ = $customer->cpf_cnpj; // Check length for CPF logic
        $std->indIEDest = $customer->indicador_ie; // 9
        $nfe->tagdest($std);

        // Products (Loop)
        $valorTotal = 0;
        foreach ($items as $i => $item) {
            $prod = new \stdClass();
            $prod->item = $i + 1;
            $prod->cProd = $item['codigo_sku'] ?? 'GENERIC';
            $prod->cEAN = "SEM GTIN";
            $prod->xProd = $item['nome'];
            $prod->NCM = $item['ncm'];
            $prod->CFOP = '5102'; // Mock
            $prod->uCom = $item['unidade'];
            $prod->qCom = 1; // Quantity
            $prod->vUnCom = $item['preco_venda'];
            $prod->vProd = $item['preco_venda'];
            $prod->cEANTrib = "SEM GTIN";
            $prod->uTrib = $item['unidade'];
            $prod->qTrib = 1;
            $prod->vUnTrib = $item['preco_venda'];
            $prod->indTot = 1;
            $nfe->tagprod($prod);

            $valorTotal += $item['preco_venda'];
        }

        // Totals
        // (Simplified for POC, normally requires tax calculation)

        // 4. Generate & Sign
        try {
            $xml = $nfe->getXML(); // Generates XML structure
            $signedXml = $tools->signNFe($xml); // Signs XML
        } catch (\Exception $e) {
            throw new Exception("Erro ao gerar XML: " . $e->getMessage());
        }

        // 5. Save
        $path = "xmls/signed_{$std->nNF}.xml";
        Storage::put($path, $signedXml);

        return Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => $std->nNF,
            'serie' => $std->serie,
            'chave' => 'mock_chave_44_chars_' . rand(1000, 9999), // Key logic is complex, skipping for POC
            'xml_path' => $path,
            'status' => 'signed',
            'valor_total' => $valorTotal,
        ]);
    }

    public function transmit(Nfe $nfe): array
    {
        // 1. Load Certificate and Tools (Duplicated logic, should refactor in real app)
        $company = $nfe->company;
        $pfxContent = Storage::get($company->certificate->path);
        $certificate = Certificate::readPfx($pfxContent, $company->certificate->password);

        $tools = new Tools(json_encode([
            "atualizacao" => "2023-01-01 00:00:00",
            "tpAmb" => 2,
            "razaosocial" => $company->razao_social,
            "cnpj" => $company->cnpj,
            "siglaUF" => $company->address->uf,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ]), $certificate);

        // 2. Load signed XML
        $xml = Storage::get($nfe->xml_path);

        // 3. Send to SEFAZ
        try {
            $idLote = substr(str_replace(',', '', number_format(microtime(true) * 1000000, 0, '', '')), 0, 15);
            $resp = $tools->sefazEnviaLote([$xml], $idLote);

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 103) { // 103 = Batch Received
                // Error handling
                $nfe->update([
                    'status' => 'rejected',
                    'mensagem_sefaz' => "{$std->cStat} - {$std->xMotivo}"
                ]);
                throw new Exception("Erro SEFAZ: {$std->cStat} - {$std->xMotivo}");
            }

            $recibo = $std->infRec->nRec;

            // 4. Consult Receipt (Simplified synchronous wait for POC)
            sleep(2); // Wait a bit for processing
            $protocolo = $tools->sefazConsultaRecibo($recibo);
            $stdProt = $st->toStd($protocolo);

            if ($stdProt->cStat != 104) { // 104 = Processed
                throw new Exception("Lote não processado ainda: {$stdProt->cStat} - {$stdProt->xMotivo}");
            }

            // Check final status of the Note
            $protEvent = $stdProt->protNFe->infProt;

            if ($protEvent->cStat == 100) { // Authorized
                $nfe->update([
                    'status' => 'authorized',
                    'protocolo' => $protEvent->nProt,
                    'mensagem_sefaz' => 'Autorizado o uso da NF-e',
                    'data_recebimento' => now(),
                ]);
            } else {
                $nfe->update([
                    'status' => 'rejected',
                    'mensagem_sefaz' => "{$protEvent->cStat} - {$protEvent->xMotivo}"
                ]);
            }

            return $nfe->toArray();

        } catch (\Exception $e) {
            // Log error
            throw $e;
        }
    }
}
