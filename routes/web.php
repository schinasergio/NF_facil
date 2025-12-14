<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Report Routes
Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export');

Route::resource('companies', \App\Http\Controllers\CompanyController::class);
Route::get('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'create'])->name('companies.certificate.create');
Route::post('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'store'])->name('companies.certificate.store');
Route::resource('customers', \App\Http\Controllers\CustomerController::class);
Route::resource('products', \App\Http\Controllers\ProductController::class);

// Inutilization
Route::get('/nfe/inutilization', [\App\Http\Controllers\InutilizationController::class, 'create'])->name('nfe.inutilization.create');
Route::post('/nfe/inutilization', [\App\Http\Controllers\InutilizationController::class, 'store'])->name('nfe.inutilization.store');

Route::resource('nfe', \App\Http\Controllers\NFeController::class);
Route::post('nfe/{nfe}/transmit', [\App\Http\Controllers\NFeController::class, 'transmit'])->name('nfe.transmit');
Route::get('nfe/{nfe}/pdf', [\App\Http\Controllers\NFeController::class, 'downloadPdf'])->name('nfe.pdf');
Route::get('nfe/{nfe}/view', [\App\Http\Controllers\NFeController::class, 'viewPdf'])->name('nfe.view');
Route::post('nfe/{nfe}/cancel', [\App\Http\Controllers\NFeController::class, 'cancel'])->name('nfe.cancel');
Route::get('nfe/{nfe}/correction', [\App\Http\Controllers\NFeController::class, 'correction'])->name('nfe.correction');
Route::post('nfe/{nfe}/correction', [\App\Http\Controllers\NFeController::class, 'storeCorrection'])->name('nfe.correction.store');

