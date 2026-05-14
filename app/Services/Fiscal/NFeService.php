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
use Illuminate\Support\Facades\Log;
use App\Events\NFeAuthorized;
use Exception;
use NFePHP\Common\Keys;
use Illuminate\Support\Facades\DB;
use App\Models\NfeNumber;

/**
 * Class NFeService
 * 
 * Handles NFe generation, signing, transmission, cancellation, and corrections.
 * Integrates with SEFAZ via NFePHP.
 * 
 * @package App\Services\Fiscal
 */
class NFeService
{
    /**
     * Generate and Sign a new NFe.
     *
     * @param Company  $company  The emitter company.
     * @param Customer $customer The recipient customer.
     * @param array    $items    List of items (products).
     * @return Nfe The created and signed NFe model.
     * @throws Exception If generation or signing fails.
     */
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
        $ufMapping = [
            'AC' => 12, 'AL' => 27, 'AP' => 16, 'AM' => 13, 'BA' => 29, 'CE' => 23, 'DF' => 53,
            'ES' => 32, 'GO' => 52, 'MA' => 21, 'MT' => 51, 'MS' => 50, 'MG' => 31, 'PA' => 15,
            'PB' => 25, 'PR' => 41, 'PE' => 26, 'PI' => 22, 'RJ' => 33, 'RN' => 24, 'RS' => 43,
            'RO' => 11, 'RR' => 14, 'SC' => 42, 'SP' => 35, 'SE' => 28, 'TO' => 17
        ];
        $ufEmitente = $company->address->uf ?? 'SP';
        $cUF = $ufMapping[$ufEmitente] ?? 35;
        $cMunFG = $company->address->codigo_ibge ?? 3550308; // Fallback to SP if empty
        $ambiente = $company->ambiente ?? 2; // 2=Homologation

        // Fetch Next NFe Number with Pessimistic Locking
        $nfeNumberRecord = DB::transaction(function () use ($company, $ambiente) {
            $record = NfeNumber::lockForUpdate()->firstOrCreate(
                ['company_id' => $company->id, 'ambiente' => $ambiente, 'serie' => 1],
                ['numero' => 0]
            );
            $record->numero += 1;
            $record->save();
            return $record;
        });

        $nNF = $nfeNumberRecord->numero;
        $serie = $nfeNumberRecord->serie;
        $ano = date('y');
        $mes = date('m');
        $tpEmis = 1; // 1=Normal
        $mod = 55; // NF-e

        $chave = Keys::build(
            $cUF, $ano, $mes, $company->cnpj, $mod, $serie, $nNF, $tpEmis
        );
        $cDV = substr($chave, -1);
        $cNF = substr($chave, 35, 8); // cNF is the random 8 digits before DV

        $isInterstate = $company->address->uf !== $customer->address->uf;
        $naturezaOperacao = 'Venda de Mercadoria';

        $nfe = new Make();
        $std = new \stdClass();
        $std->versao = '4.00';
        $nfe->taginfNFe($std);

        $std = new \stdClass();
        $std->cUF = $cUF;
        $std->natOp = $naturezaOperacao;
        $std->mod = $mod;
        $std->serie = $serie;
        $std->nNF = $nNF;
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = $isInterstate ? 2 : 1; // 1=Internal, 2=Interstate
        $std->cMunFG = $cMunFG;
        $std->tpImp = 1;
        $std->tpEmis = $tpEmis; // 1=Normal
        $std->cDV = $cDV;
        $std->cNF = $cNF;
        $std->tpAmb = $ambiente;
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
            $prod->CFOP = $isInterstate ? '6102' : '5102'; // Venda (Interestadual/Interna)
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

        // 4. Validate, Generate & Sign
        try {
            $xml = $nfe->getXML(); // Generates XML structure

            // Validate against XSD
            $xsdPath = base_path('vendor/nfephp-org/sped-nfe/schemes/PL_009_V4/nfe_v4.00.xsd');
            try {
                \NFePHP\Common\Validator::isValid($xml, $xsdPath);
            } catch (\NFePHP\Common\Exception\ValidatorException $e) {
                Log::error("XML Schema Validation Failed", ['errors' => $e->getMessage()]);
                throw new Exception("XML Inválido estruturalmente (Validação XSD): " . $e->getMessage());
            }

            $signedXml = $tools->signNFe($xml); // Signs XML
            Log::info("NFe Generated and Signed", ['company_id' => $company->id, 'customer_id' => $customer->id, 'nNF' => $nNF]);
        } catch (\Exception $e) {
            Log::error("Error generating NFe", ['error' => $e->getMessage(), 'company_id' => $company->id]);
            throw new Exception("Erro ao gerar XML: " . $e->getMessage());
        }

        // 5. Save
        $path = "xmls/signed_{$std->nNF}.xml";
        Storage::put($path, $signedXml);

        $nfeObj = Nfe::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'numero' => $std->nNF,
            'serie' => $std->serie,
            'chave' => $chave,
            'xml_path' => $path,
            'status' => 'signed',
            'valor_total' => $valorTotal,
        ]);

        $this->createLog($nfeObj, 'signed', 'NFe Gerada e Assinada', null, $signedXml);

        return $nfeObj;
    }

    /**
     * Transmit the NFe to SEFAZ.
     *
     * @param Nfe $nfe The NFe to transmit.
     * @return array The updated NFe data as array.
     * @throws Exception If SEFAZ rejects or communication error.
     */
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
            Log::info("Transmitting NFe", ['nfe_id' => $nfe->id, 'chave' => $nfe->chave]);
            $idLote = substr(str_replace(',', '', number_format(microtime(true) * 1000000, 0, '', '')), 0, 15);

            $this->createLog($nfe, 'sending_batch', 'Enviando Lote para SEFAZ', null, $xml);

            $resp = $tools->sefazEnviaLote([$xml], $idLote);

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 103) { // 103 = Batch Received
                // Error handling
                $this->createLog($nfe, 'batch_error', "{$std->cStat} - {$std->xMotivo}", null, $resp);
                $nfe->update([
                    'status' => 'rejected',
                    'mensagem_sefaz' => "{$std->cStat} - {$std->xMotivo}"
                ]);
                Log::error("SEFAZ Batch Rejection", ['nfe_id' => $nfe->id, 'cStat' => $std->cStat, 'xMotivo' => $std->xMotivo]);
                throw new Exception("Erro SEFAZ: {$std->cStat} - {$std->xMotivo}");
            }

            $recibo = $std->infRec->nRec;
            $this->createLog($nfe, 'batch_received', "Lote recebido. Recibo: {$recibo}", $recibo, $resp);

            // 4. Consult Receipt (Simplified synchronous wait for POC)
            sleep(2); // Wait a bit for processing
            $protocolo = $tools->sefazConsultaRecibo($recibo);
            $stdProt = $st->toStd($protocolo);

            if ($stdProt->cStat != 104) { // 104 = Processed
                Log::error("SEFAZ Receipt Processing Error", ['nfe_id' => $nfe->id, 'cStat' => $stdProt->cStat]);
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
                $this->createLog($nfe, 'authorized', 'Autorizado uso do documento fiscal', $protEvent->nProt, $protocolo);
                Log::info("NFe Authorized", ['nfe_id' => $nfe->id, 'protocolo' => $protEvent->nProt]);

                // Dispatch Event to send Email
                NFeAuthorized::dispatch($nfe);
            } else {
                $nfe->update([
                    'status' => 'rejected',
                    'mensagem_sefaz' => "{$protEvent->cStat} - {$protEvent->xMotivo}"
                ]);
                $this->createLog($nfe, 'rejected', "{$protEvent->cStat} - {$protEvent->xMotivo}", null, $protocolo);
                Log::warning("NFe Rejected", ['nfe_id' => $nfe->id, 'cStat' => $protEvent->cStat, 'xMotivo' => $protEvent->xMotivo]);
            }

            return $nfe->toArray();

        } catch (\Exception $e) {
            // Log error
            Log::error("Exception transmitting NFe", ['nfe_id' => $nfe->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cancel an authorized NFe.
     *
     * @param Nfe    $nfe           The NFe to cancel.
     * @param string $justification Justification for cancellation (min 15 chars).
     * @return array The updated NFe data.
     * @throws Exception If status is invalid or SEFAZ rejects.
     */
    public function cancel(Nfe $nfe, string $justification): array
    {
        // 1. Initial Checks
        if ($nfe->status !== 'authorized') {
            throw new Exception("Apenas NFes autorizadas podem ser canceladas.");
        }
        if (strlen($justification) < 15) {
            throw new Exception("A justificativa deve ter no mínimo 15 caracteres.");
        }

        // 2. Obtain Tools
        $tools = $this->getTools($nfe->company);

        // 3. Send Cancellation Event
        try {
            Log::info("Canceling NFe", ['nfe_id' => $nfe->id, 'justification' => $justification]);
            $chave = $nfe->chave;
            $nProt = $nfe->protocolo;
            $response = $tools->sefazCancela($chave, $justification, $nProt);

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            // Check Event Status (cStat 135 = Evento registrado e vinculado a NF-e)
            if ($std->infEvento->cStat == 135) {
                $nfe->update([
                    'status' => 'canceled',
                    'mensagem_sefaz' => 'Cancelamento homologado'
                ]);
                $this->createLog($nfe, 'canceled', 'Cancelamento homologado pelo emitente', $std->infEvento->nProt ?? null, $response);
                Log::info("NFe Canceled Successfully", ['nfe_id' => $nfe->id]);
                return $nfe->toArray();
            } else {
                Log::error("NFe Cancellation Failed", ['nfe_id' => $nfe->id, 'cStat' => $std->infEvento->cStat]);
                throw new Exception("Erro ao cancelar: {$std->infEvento->cStat} - {$std->infEvento->xMotivo}");
            }

        } catch (\Exception $e) {
            Log::error("Exception canceling NFe", ['nfe_id' => $nfe->id, 'error' => $e->getMessage()]);
            throw new Exception("Erro no Cancelamento: " . $e->getMessage());
        }
    }

    /**
     * Send a Correction Letter (CC-e) for an NFe.
     *
     * @param Nfe    $nfe            The NFe to correct.
     * @param string $correctionData The correction text (min 15 chars).
     * @return array Result with status and message.
     * @throws Exception If status is invalid or SEFAZ rejects.
     */
    public function correction(Nfe $nfe, string $correctionData): array
    {
        // 1. Initial Checks
        if ($nfe->status !== 'authorized') {
            throw new Exception("Apenas NFes autorizadas podem receber carta de correção.");
        }
        if (strlen($correctionData) < 15) {
            throw new Exception("A correção deve ter no mínimo 15 caracteres.");
        }

        // 2. Obtain Tools
        $tools = $this->getTools($nfe->company);

        // 3. Send CC-e Event
        try {
            Log::info("Sending CC-e", ['nfe_id' => $nfe->id]);
            $chave = $nfe->chave;
            $nSeqEvento = 1; // Simplification: assuming first correction. In real app, query max nSeqEvento from DB.

            $response = $tools->sefazCCe($chave, $correctionData, $nSeqEvento);

            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($response);

            // Check Event Status (cStat 135 = Evento registrado e vinculado a NF-e)
            if ($std->infEvento->cStat == 135) {
                // We don't change NFe status, just log/notify. 
                // Optionally save event XML.
                Log::info("CC-e Linked Successfully", ['nfe_id' => $nfe->id]);
                $this->createLog($nfe, 'corrected', 'Carta de Correção vinculada', $std->infEvento->nProt ?? null, $response);
                return ['status' => 'corrected', 'message' => 'Carta de Correção vinculada com sucesso.'];
            } else {
                Log::error("CC-e Failed", ['nfe_id' => $nfe->id, 'cStat' => $std->infEvento->cStat]);
                throw new Exception("Erro na CC-e: {$std->infEvento->cStat} - {$std->infEvento->xMotivo}");
            }

        } catch (\Exception $e) {
            Log::error("Exception in CC-e", ['nfe_id' => $nfe->id, 'error' => $e->getMessage()]);
            throw new Exception("Erro na Carta de Correção: " . $e->getMessage());
        }
    }

    /**
     * Initialize NFePHP Tools instance.
     *
     * @param Company $company The company to use.
     * @return Tools Configured Tools instance.
     */
    private function getTools(Company $company): Tools
    {
        $pfxContent = Storage::get($company->certificate->path);
        $certificate = Certificate::readPfx($pfxContent, $company->certificate->password);

        return new Tools(json_encode([
            "atualizacao" => "2023-01-01 00:00:00",
            "tpAmb" => 2,
            "razaosocial" => $company->razao_social,
            "cnpj" => $company->cnpj,
            "siglaUF" => $company->address->uf,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
        ]), $certificate);
    }

    /**
     * Helper to create a log entry.
     */
    private function createLog(Nfe $nfe, string $status, string $message, ?string $protocolo = null, $payload = null): void
    {
        try {
            \App\Models\NfeLog::create([
                'nfe_id' => $nfe->id,
                'status' => $status,
                'message' => $message,
                'protocolo' => $protocolo,
                'payload' => is_string($payload) ? $payload : json_encode($payload),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create NFe Log", ['error' => $e->getMessage()]);
        }
    }
}
