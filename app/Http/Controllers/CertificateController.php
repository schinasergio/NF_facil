<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Fiscal\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Show the form for uploading a certificate.
     */
    public function create(Company $company)
    {
        return view('certificates.create', compact('company'));
    }

    /**
     * Store the uploaded certificate.
     */
    public function store(Request $request, Company $company)
    {
        $request->validate([
            'pfx_file' => 'required|file|mimetypes:application/x-pkcs12,application/octet-stream',
            'password' => 'required|string',
        ]);

        try {
            $this->certificateService->uploadCertificate(
                $company,
                $request->file('pfx_file'),
                $request->input('password')
            );
            return redirect()->route('companies.index')->with('success', 'Certificado digital importado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['pfx_file' => $e->getMessage()]);
        }
    }
}
