<?php

namespace App\Services\Fiscal;

use App\Models\Company;
use App\Models\NfeInutilization;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class InutilizationService
 * 
 * Handles Number Voiding (Inutilização) with SEFAZ.
 * 
 * @package App\Services\Fiscal
 */
class InutilizationService
{
    /**
     * Initialize NFePHP Tools.
     *
     * @param Company $company The company.
     * @return Tools Configured Tools.
     */
    private function getTools(Company $company): Tools
    {
        $pfxContent = Storage::get($company->certificate->path);
        $certificate = Certificate::readPfx($pfxContent, $company->certificate->password);

        $tools = new Tools(json_encode([
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => 2, // 1-Produção, 2-Homologação
            "razaosocial" => $company->razao_social,
            "siglaUF" => $company->address->uf,
            "cnpj" => $company->cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "GPB0JBWLUR6HWFTVEJ666S9OPZA6M865",
            "CSCid" => "000002"
        ]), $certificate);

        $tools->model('55');

        return $tools;
    }

    /**
     * Inutilize a range of NFe numbers.
     *
     * @param Company $company       The company.
     * @param int     $serie         NFe series.
     * @param int     $start         Start number.
     * @param int     $end           End number.
     * @param string  $justification Justification (min 15 chars).
     * @return array Result array with status, protocol, or error message.
     */
    public function inutilize(Company $company, int $serie, int $start, int $end, string $justification): array
    {
        try {
            Log::info("Inutilizing Numbers", ['company_id' => $company->id, 'serie' => $serie, 'start' => $start, 'end' => $end]);
            $tools = $this->getTools($company);

            $xml = $tools->sefazInutiliza(
                $serie,
                $start,
                $end,
                $justification
            );

            // Parse response
            $st = new \NFePHP\NFe\Common\Standardize();
            $std = $st->toStd($xml);

            $status = ($std->infInut->cStat == '102') ? 'authorized' : 'rejected';
            $protocolo = $std->infInut->nProt ?? null;

            // Save record
            $inut = NfeInutilization::create([
                'company_id' => $company->id,
                'serie' => $serie,
                'numero_inicial' => $start,
                'numero_final' => $end,
                'justificativa' => $justification,
                'protocolo' => $protocolo,
                'status' => $status,
                'xml_path' => "xmls/inut_{$company->id}_{$serie}_{$start}_{$end}.xml"
            ]);

            Storage::put($inut->xml_path, $xml);

            Log::info("Inutilization Processed", ['status' => $status, 'protocolo' => $protocolo]);

            return [
                'status' => $status,
                'message' => $std->infInut->xMotivo,
                'protocolo' => $protocolo,
                'record' => $inut
            ];

        } catch (Exception $e) {
            Log::error("Error Inutilizing", ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
