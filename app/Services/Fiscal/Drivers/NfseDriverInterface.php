<?php

namespace App\Services\Fiscal\Drivers;

use App\Models\Company;

interface NfseDriverInterface
{
    /**
     * Set the Company context for the driver (Certificate, Env, etc)
     */
    public function setCompany(Company $company): void;

    /**
     * Generate the RPS XML
     * @param array $data Service data
     * @return string XML content
     */
    public function gerarRps(array $data): string;

    /**
     * Transmit the XML to the Municipality
     * @param string $xml Signed XML
     * @return array ['success' => bool, 'protocol' => string, 'message' => string]
     */
    public function transmitir(string $xml): array;

    /**
     * Consult status by Protocol or RPS Number
     */
    public function consultar(string $protocolo): array;

    /**
     * Cancel an authorized NFS-e
     */
    public function cancelar(string $numeroNfse, string $motivo): array;
}
