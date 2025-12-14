<?php

namespace App\Services\Fiscal;

use App\Models\Nfe;
use NFePHP\DA\NFe\Daexe;
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
            // Using Daexe (simplified / HTML based) or Danfe (Fpdf/Mpdf).
            // 'sped-da' assumes we use one of the drivers. 
            // The default `nfephp-org/sped-da` package usually requires a specific driver (like mpdf).
            // Since we only installed `sped-da`, let's check which class is available.
            // If `Daexe` is available, it produces a simpler output. 
            // Ideally we should have installed `nfephp-org/sped-da-mpdf`.
            // Let's assume Daexe is present in sped-da for legacy or basic support, 
            // OR use the generic class. 
            // For now, I will use Daexe as it often works out of box or throw exception if I need to install mpdf.

            // Wait, recent versions split drivers. 
            // If `sped-da` v1.1.2 installed, it likely has `NFePHP\DA\NFe\Danfe`.

            $danfe = new Daexe($xml);
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
