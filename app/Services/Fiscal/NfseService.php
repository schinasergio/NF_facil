<?php

namespace App\Services\Fiscal;

use App\Models\Company;
use App\Services\Fiscal\Drivers\NfseDriverInterface;
use App\Services\Fiscal\Drivers\SaoPauloDriver;
use App\Services\Fiscal\Drivers\SantosDriver;
use Exception;

class NfseService
{
    /**
     * Factory Method to get the correct Driver
     */
    public function getDriver(Company $company): NfseDriverInterface
    {
        // Ensure company has address loaded
        if (!$company->address) {
            $company->load('address');
        }

        if (!$company->address) {
            throw new Exception("Empresa sem endereço cadastrado. Não é possível identificar a prefeitura.");
        }

        $ibge = $company->address->codigo_ibge;

        // Factory Logic
        $driver = match ($ibge) {
            '3550308' => new SaoPauloDriver(), // São Paulo
            '3548500' => new SantosDriver(),   // Santos
            default => throw new Exception("Prefeitura não homologada para emissão NFS-e (IBGE: $ibge)."),
        };

        $driver->setCompany($company);
        return $driver;
    }

    /**
     * Main Entry Point for Emission
     */
    public function emitir(Company $company, array $data)
    {
        $driver = $this->getDriver($company);

        // 1. Generate XML
        $xml = $driver->gerarRps($data);

        // 2. Transmit
        $response = $driver->transmitir($xml);

        return $response;
    }
}
