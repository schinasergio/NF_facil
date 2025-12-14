<?php

namespace App\Services\Fiscal;

use App\Models\Nfe;
use NFePHP\DA\NFe\Danfe;
use Illuminate\Support\Facades\Storage;
use Exception;

class DanfeService
{
    public function generatePdf(Nfe $nfe): string
    {
        try {
            // 1. Load XML
            if (!Storage::exists($nfe->xml_path)) {
                throw new Exception("XML não encontrado para esta NFe.");
            }
            $xml = Storage::get($nfe->xml_path);

            // 2. Generate DANFE
            $danfe = new Danfe($xml);
            $danfe->debugMode(true);
            $danfe->creditsIntegratorFooter('NF-Facil System');

            // Render
            $pdf = $danfe->render(); // Returns PDF binary string

            return $pdf;

        } catch (\Exception $e) {
            throw new Exception("Erro ao gerar DANFE: " . $e->getMessage());
        }
    }
}
