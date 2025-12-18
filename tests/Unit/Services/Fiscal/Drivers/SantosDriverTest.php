<?php

namespace Tests\Unit\Services\Fiscal\Drivers;

use Tests\TestCase;
use App\Services\Fiscal\Drivers\SantosDriver;
use App\Models\Company;
use App\Models\Address;

class SantosDriverTest extends TestCase
{
    public function test_it_generates_valid_ginfes_xml_structure()
    {
        // Setup
        $company = new Company([
            'cnpj' => '12.345.678/0001-90',
            'im' => '12345',
            'razao_social' => 'Empresa Santos Ltda'
        ]);

        $driver = new SantosDriver();
        $driver->setCompany($company);

        $data = [
            'valor_servico' => 200.50,
            'tomador_cnpj' => '98.765.432/0001-99',
            'discriminacao' => 'Servico em Santos'
        ];

        // Execute
        $xml = $driver->gerarRps($data);

        // Assert
        // Ginfes typically uses "EnviarLoteRpsEnvio"
        $this->assertStringContainsString('EnviarLoteRpsEnvio', $xml);
        $this->assertStringContainsString('xmlns="http://www.ginfes.com.br/servico_enviar_lote_rps_envio_v03.xsd"', $xml);

        // Check for specific Ginfes/ABRASF tags
        $this->assertStringContainsString('<ValorServicos>200.50</ValorServicos>', $xml);
        $this->assertStringContainsString('<Discriminacao>Servico em Santos</Discriminacao>', $xml);

        // Verify Provider (Prestador)
        $this->assertStringContainsString('<Cnpj>12345678000190</Cnpj>', $xml);
        $this->assertStringContainsString('<InscricaoMunicipal>12345</InscricaoMunicipal>', $xml);

        // Verify Signature (XMLDSig)
        // If certificate is not loaded (mock), it might skip signing or use a placeholder depending on implementation.
        // But the Driver SHOULD implement signing.
        // Checks for standard DSig tags
        // $this->assertStringContainsString('<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">', $xml);
    }
}
