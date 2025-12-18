<?php

namespace Tests\Unit\Services\Fiscal\Drivers;

use Tests\TestCase;
use App\Services\Fiscal\Drivers\SaoPauloDriver;
use App\Models\Company;
use App\Models\Address;
use Mockery;

class SaoPauloDriverTest extends TestCase
{
    public function test_it_generates_valid_xml_structure_for_sao_paulo()
    {
        // Setup
        $company = new Company([
            'cnpj' => '12.345.678/0001-90',
            'im' => '12345678',
            'razao_social' => 'Empresa Teste Ltda'
        ]);

        // Mock Address needed for Factory, but here we test Driver directly
        $driver = new SaoPauloDriver();
        $driver->setCompany($company);

        $data = [
            'valor_servico' => 100.00,
            'tomador_cnpj' => '98.765.432/0001-99',
            'discriminacao' => 'Teste Unitario de XML'
        ];

        // Execute
        $xml = $driver->gerarRps($data);

        // Assert
        $this->assertStringContainsString('<p1:PedidoEnvioRPS', $xml);
        $this->assertStringContainsString('<CNPJ>12345678000190</CNPJ>', $xml); // Remetente Cleaned
        $this->assertStringContainsString('<ValorServicos>100.00</ValorServicos>', $xml);
        $this->assertStringContainsString('<Assinatura>', $xml);

        // Check if Signature is NOT the placeholder 'SIGNATURE_PLACEHOLDER'
        $this->assertStringNotContainsString('SIGNATURE_PLACEHOLDER', $xml);
        // It should be MOCK-SIGNATURE (if no cert) or Base64 (if cert)
        // Since we didn't provide a cert, it should be the fallback MOCK-SIGNATURE
        $this->assertStringContainsString('MOCK-SIGNATURE-', $xml);
    }
}
