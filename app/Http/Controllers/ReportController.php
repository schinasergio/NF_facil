<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Class ReportController
 * 
 * Manages Report generation requests.
 * Allows users to view filtered NFe lists and export them to CSV.
 * 
 * @package App\Http\Controllers
 */
class ReportController extends Controller
{
    /**
     * @var ReportService
     */
    protected $reportService;

    /**
     * ReportController constructor.
     * 
     * @param ReportService $reportService Service for report logic.
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the report filter form and results.
     * 
     * @param Request $request Request containing filter parameters.
     * @return \Illuminate\View\View Report view with results and filter options.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['start_date', 'end_date', 'status', 'customer_id']);

        $nfes = $this->reportService->buildQuery($filters)->paginate(15)->withQueryString();

        $customers = Customer::orderBy('razao_social')->get();

        return view('reports.index', compact('nfes', 'customers', 'filters'));
    }

    /**
     * Export the filtered report to a CSV file.
     * 
     * @param Request $request Request containing filter parameters.
     * @return \Illuminate\Http\Response Streamed CSV download.
     */
    public function exportCsv(Request $request): Response
    {
        $filters = $request->only(['start_date', 'end_date', 'status', 'customer_id']);

        $nfes = $this->reportService->buildQuery($filters)->get();
        $csvContent = $this->reportService->generateCsv($nfes);

        $filename = 'relatorio_nfe_' . date('Y_m_d_H_i') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
