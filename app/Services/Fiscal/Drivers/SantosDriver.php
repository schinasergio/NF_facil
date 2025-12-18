<?php

namespace App\Services\Fiscal\Drivers;

use App\Models\Company;
use Exception;

class SantosDriver implements NfseDriverInterface
{
    protected Company $company;

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function gerarRps(array $data): string
    {
        // TODO: Implementar XML Ginfes
        return '<?xml version="1.0"?><RPS_Santos>Simulacao</RPS_Santos>';
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
