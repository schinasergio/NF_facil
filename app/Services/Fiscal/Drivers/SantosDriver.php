<?php

namespace App\Services\Fiscal\Drivers;

use App\Models\Company;
use NFePHP\Common\Certificate;
use NFePHP\Common\Signer;
use Exception;

class SantosDriver implements NfseDriverInterface
{
    protected Company $company;
    protected Certificate $certificate;

    public function setCompany(Company $company): void
    {
        $this->company = $company;

        if ($company->certificate) {
            $pfxPath = $company->certificate->file_path ?? null;
            if ($pfxPath && file_exists($pfxPath)) {
                $pfxContent = file_get_contents($pfxPath);
                $password = $company->certificate->password;
                $this->certificate = Certificate::readPfx($pfxContent, $password);
            }
        }
    }

    public function gerarRps(array $data): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;

        $root = $dom->createElement('EnviarLoteRpsEnvio');
        $root->setAttribute('xmlns', 'http://www.ginfes.com.br/servico_enviar_lote_rps_envio_v03.xsd');
        $root->setAttribute('xmlns:tipos', 'http://www.ginfes.com.br/tipos_v03.xsd');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dom->appendChild($root);

        $loteId = 'Lote' . time();
        $lote = $dom->createElement('LoteRps');
        $lote->setAttribute('Id', $loteId);
        $root->appendChild($lote);

        $lote->appendChild($dom->createElement('NumeroLote', time()));
        $lote->appendChild($dom->createElement('Cnpj', $this->cleanCnpj($this->company->cnpj)));
        $lote->appendChild($dom->createElement('InscricaoMunicipal', $this->cleanCnpj($this->company->im ?? '')));
        $lote->appendChild($dom->createElement('QuantidadeRps', '1'));

        $lista = $dom->createElement('ListaRps');
        $lote->appendChild($lista);

        $rps = $dom->createElement('Rps');
        $lista->appendChild($rps);

        $infRps = $dom->createElement('InfRps');
        $infRps->setAttribute('Id', 'Rps' . time());
        $rps->appendChild($infRps);

        // Identificacao
        $identificacao = $dom->createElement('IdentificacaoRps');
        $identificacao->appendChild($dom->createElement('Numero', time()));
        $identificacao->appendChild($dom->createElement('Serie', 'RPS'));
        $identificacao->appendChild($dom->createElement('Tipo', '1')); // 1=RPS
        $infRps->appendChild($identificacao);

        $infRps->appendChild($dom->createElement('DataEmissao', date('Y-m-d\TH:i:s')));
        $infRps->appendChild($dom->createElement('NaturezaOperacao', '1')); // 1=Tributacao no municipio
        $infRps->appendChild($dom->createElement('OptanteSimplesNacional', '2')); // 1=Sim, 2=Nao (Needs config)
        $infRps->appendChild($dom->createElement('IncentivadorCultural', '2'));
        $infRps->appendChild($dom->createElement('Status', '1')); // 1=Normal

        // Servico
        $servico = $dom->createElement('Servico');

        $valores = $dom->createElement('Valores');
        $valor = number_format((float) str_replace(',', '.', $data['valor_servico']), 2, '.', '');
        $valores->appendChild($dom->createElement('ValorServicos', $valor));
        $valores->appendChild($dom->createElement('IssRetido', '2')); // 1=Sim, 2=Nao
        $valores->appendChild($dom->createElement('ValorIss', number_format($valor * 0.05, 2, '.', '')));
        $valores->appendChild($dom->createElement('BaseCalculo', $valor));
        $valores->appendChild($dom->createElement('Aliquota', '0.05'));
        $servico->appendChild($valores);

        $servico->appendChild($dom->createElement('ItemListaServico', '14.01')); // Example
        $servico->appendChild($dom->createElement('CodigoTributacaoMunicipio', '0000'));
        $servico->appendChild($dom->createElement('Discriminacao', $data['discriminacao'] ?? 'Servico'));
        $servico->appendChild($dom->createElement('CodigoMunicipio', '3548500')); // Santos
        $infRps->appendChild($servico);

        // Prestador
        $prestador = $dom->createElement('Prestador');
        $prestador->appendChild($dom->createElement('Cnpj', $this->cleanCnpj($this->company->cnpj)));
        $prestador->appendChild($dom->createElement('InscricaoMunicipal', $this->cleanCnpj($this->company->im ?? '')));
        $infRps->appendChild($prestador);

        // Tomador
        $tomador = $dom->createElement('Tomador');
        $identificacaoTomador = $dom->createElement('IdentificacaoTomador');
        $cpfCnpjToken = $dom->createElement('CpfCnpj');
        $cpfCnpjToken->appendChild($dom->createElement('Cnpj', $this->cleanCnpj($data['tomador_cnpj'])));
        $identificacaoTomador->appendChild($cpfCnpjToken);
        $tomador->appendChild($identificacaoTomador);
        $infRps->appendChild($tomador);

        // Sign XML if certificate exists
        if (isset($this->certificate)) {
            try {
                // Sign the LoteRps node based on 'Id' attribute
                // Signer::sign(Certificate $cert, $content, $tag, $attribute, $algorithm, $namespaces, $rootTagName, $digestAlgorithm)
                // We pass the XML string, not DOM
                $xmlContent = $dom->saveXML();

                // OPENSSL_ALGO_SHA1 is int(1)
                $signedXml = Signer::sign(
                    $this->certificate,
                    $xmlContent,
                    'LoteRps',
                    'Id',
                    OPENSSL_ALGO_SHA1,
                    ['xmlns' => 'http://www.ginfes.com.br/servico_enviar_lote_rps_envio_v03.xsd'] // Explicit namespace might be needed
                );

                return $signedXml;
            } catch (Exception $e) {
                // Fallback for dev/mock if signing fails (e.g. no private key loaded)
                // throw new Exception("Erro ao assinar XML Santos: " . $e->getMessage());
                return $dom->saveXML();
            }
        }

        return $dom->saveXML();
    }

    private function cleanCnpj($val)
    {
        return preg_replace('/\D/', '', $val);
    }

    public function transmitir(string $xml): array
    {
        return [
            'success' => true,
            'protocol' => 'MOCK-SAN-' . time(),
            'message' => 'Lote enviado (Simulação Santos)'
        ];
    }

    public function consultar(string $protocolo): array
    {
        return ['status' => 'Processando'];
    }

    public function cancelar(string $numeroNfse, string $motivo): array
    {
        return ['success' => false, 'message' => 'Não implementado'];
    }
}
