<?php

namespace App\Services;

use App\Models\Nfe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class ReportService
 * 
 * Service responsible for building NFe reports and exporting data.
 * Handles filtering logic and CSV file generation.
 * 
 * @package App\Services
 */
class ReportService
{
    /**
     * Build the NFe query based on provided filters.
     * 
     * Applies filters for date range, status, and specific customer
     * to the NFe Eloquent query builder.
     * 
     * @param array $filters Associative array of filters (start_date, end_date, status, customer_id).
     * @return \Illuminate\Database\Eloquent\Builder The filtered query builder.
     */
    public function buildQuery(array $filters): Builder
    {
        $query = Nfe::query()->with(['company', 'customer']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->latest();
    }

    /**
     * Generate CSV content from a collection of NFes.
     * 
     * Iterates through the collection and formats each record as a CSV line,
     * including headers suitable for Excel opening.
     * 
     * @param \Illuminate\Support\Collection $nfes Collection of NFe models.
     * @return string The raw CSV string content.
     */
    public function generateCsv(Collection $nfes): string
    {
        $output = fopen('php://temp', 'r+');

        // Add BOM for Excel UTF-8 compatibility
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'ID',
            'Número',
            'Série',
            'Emissor',
            'Destinatário',
            'Status',
            'Valor Total',
            'Data Emissão',
            'Chave de Acesso'
        ], ';'); // Semicolon is better for Excel in some locales

        foreach ($nfes as $nfe) {
            fputcsv($output, [
                $nfe->id,
                $nfe->numero,
                $nfe->serie,
                $nfe->company->razao_social ?? 'N/A',
                $nfe->customer->razao_social ?? 'N/A',
                ucfirst($nfe->status),
                number_format($nfe->valor_total, 2, ',', '.'),
                $nfe->created_at->format('d/m/Y H:i:s'),
                $nfe->chave ?? '-'
            ], ';');
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
