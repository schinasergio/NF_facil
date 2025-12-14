<?php

namespace App\Services\Fiscal;

use App\Models\Certificate;
use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class CertificateService
{
    /**
     * Upload and validate a digital certificate.
     */
    public function uploadCertificate(Company $company, UploadedFile $file, string $password): Certificate
    {
        // 1. Validate PFX password and extract expiry
        $pfxContent = $file->get();
        if (!openssl_pkcs12_read($pfxContent, $certs, $password)) {
            throw new Exception("Senha do certificado incorreta ou arquivo inválido.");
        }

        $data = openssl_x509_parse($certs['cert']);
        $expiresAt = date('Y-m-d H:i:s', $data['validTo_time_t']);

        // 2. Store file securely
        $path = $file->store('certificates');

        // 3. Save to DB (password is encrypted by Model Cast)
        return Certificate::create([
            'company_id' => $company->id,
            'path' => $path,
            'password' => $password,
            'expires_at' => $expiresAt,
        ]);
    }
}
