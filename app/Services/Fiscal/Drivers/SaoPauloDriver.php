<?php

namespace App\Services\Fiscal\Drivers;

use App\Models\Company;
use NFePHP\Common\Certificate;
use Exception;

class SaoPauloDriver implements NfseDriverInterface
{
    protected Company $company;
    protected Certificate $certificate;

    public function setCompany(Company $company): void
    {
        $this->company = $company;

        // Load Certificate
        if ($company->certificate) {
            // Assuming pfx_content is stored or path
            // For now, mocking or using pfx_path if available
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

        $root = $dom->createElementNS('http://www.prefeitura.sp.gov.br/nfe', 'p1:PedidoEnvioRPS');
        $root->setAttribute('xmlns:p1', 'http://www.prefeitura.sp.gov.br/nfe');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dom->appendChild($root);

        // Cabecalho
        $cabecalho = $dom->createElement('Cabecalho');
        $cabecalho->setAttribute('Versao', '1');
        $root->appendChild($cabecalho);

        $cpfCnpjRemetente = $dom->createElement('CPFCNPJRemetente');
        $cnpj = $dom->createElement('CNPJ', $this->cleanCnpj($this->company->cnpj));
        $cpfCnpjRemetente->appendChild($cnpj);
        $cabecalho->appendChild($cpfCnpjRemetente);

        // RPS
        $rps = $dom->createElement('RPS');
        $root->appendChild($rps);

        $numeroRps = time(); // Temporary: In production use DB sequence

        // Assinatura
        $assinatura = $this->signRps([
            'im' => $this->cleanCnpj($this->company->im ?? '00000000'),
            'serie' => 'RPS',
            'numero' => $numeroRps,
            'data' => date('Ymd'),
            'tributacao' => 'T',
            'status' => 'N',
            'iss_retido' => 'N',
            'valor' => number_format((float) str_replace(',', '.', $data['valor_servico']), 2, '', ''),
            'codigo_servico' => '02698', // Default for test
            'cpf_cnpj_tomador' => $this->cleanCnpj($data['tomador_cnpj'])
        ]);

        $rps->appendChild($dom->createElement('Assinatura', $assinatura));

        // ChaveRPS
        $chave = $dom->createElement('ChaveRPS');
        $chave->appendChild($dom->createElement('InscricaoPrestador', $this->cleanCnpj($this->company->im ?? '00000000')));
        $chave->appendChild($dom->createElement('SerieRPS', 'RPS'));
        $chave->appendChild($dom->createElement('NumeroRPS', $numeroRps));
        $rps->appendChild($chave);

        $rps->appendChild($dom->createElement('TipoRPS', 'RPS'));
        $rps->appendChild($dom->createElement('DataEmissao', date('Y-m-d')));
        $rps->appendChild($dom->createElement('StatusRPS', 'N'));
        $rps->appendChild($dom->createElement('TributacaoRPS', 'T'));

        // Valores
        $valorServico = number_format((float) str_replace(',', '.', $data['valor_servico']), 2, '.', '');
        $rps->appendChild($dom->createElement('ValorServicos', $valorServico));
        $rps->appendChild($dom->createElement('ValorDeducoes', '0.00'));
        $rps->appendChild($dom->createElement('ValorPIS', '0.00'));
        $rps->appendChild($dom->createElement('ValorCOFINS', '0.00'));
        $rps->appendChild($dom->createElement('ValorINSS', '0.00'));
        $rps->appendChild($dom->createElement('ValorIR', '0.00'));
        $rps->appendChild($dom->createElement('ValorCSLL', '0.00'));

        $rps->appendChild($dom->createElement('CodigoServico', '02698'));
        $rps->appendChild($dom->createElement('AliquotaServicos', '0.05'));
        $rps->appendChild($dom->createElement('IssRetido', 'false')); // boolean string? Check XSD. Usually F/false/N.

        // Tomador
        $cpfCnpjTomador = $dom->createElement('CPFCNPJTomador');
        $cpfCnpjTomador->appendChild($dom->createElement('CNPJ', $this->cleanCnpj($data['tomador_cnpj'])));
        $rps->appendChild($cpfCnpjTomador);

        // Discriminacao
        $rps->appendChild($dom->createElement('Discriminacao', $data['discriminacao'] ?? 'Serviço Prestado'));

        return $dom->saveXML();
    }

    private function signRps(array $fields): string
    {
        // Format: IM(8) + Serie(5) + Numero(12) + Data(8) + Trib(1) + Status(1) + ISS(1) + Valor(15) + Codigo(5) + CPFCNPJTomador(14)
        $string = str_pad($fields['im'], 8, '0', STR_PAD_LEFT);
        $string .= str_pad($fields['serie'], 5, ' ', STR_PAD_RIGHT);
        $string .= str_pad($fields['numero'], 12, '0', STR_PAD_LEFT);
        $string .= $fields['data'];
        $string .= $fields['tributacao'];
        $string .= $fields['status'];
        $string .= $fields['iss_retido'];
        $string .= str_pad($fields['valor'], 15, '0', STR_PAD_LEFT);
        $string .= str_pad($fields['codigo_servico'], 5, '0', STR_PAD_LEFT);
        $string .= str_pad($fields['cpf_cnpj_tomador'], 14, '0', STR_PAD_LEFT);

        if (!isset($this->certificate)) {
            // Fallback for testing without cert
            return 'MOCK-SIGNATURE-' . md5($string);
        }

        $signature = '';
        $algo = OPENSSL_ALGO_SHA1;
        // Verify if privateKey is available directly or needs extraction
        $pkey = $this->certificate->privateKey;
        if (!$pkey) {
            throw new Exception("Chave privada não encontrada no certificado.");
        }

        openssl_sign($string, $signature, $pkey, $algo);

        return base64_encode($signature);
    }

    private function cleanCnpj($val)
    {
        return preg_replace('/\D/', '', $val);
    }

    public function transmitir(string $xml): array
    {
        // TODO: Implementar SOAP Curl
        return [
            'success' => true,
            'protocol' => 'MOCK-SP-' . time(),
            'message' => 'Lote enviado (Simulação SP) - XML Assinado'
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
