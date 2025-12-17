<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InutilizationController;
use App\Http\Controllers\NFeController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Report Routes
Route::middleware('auth')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

    Route::resource('companies', CompanyController::class);

    Route::get('companies/{company}/certificate', [CertificateController::class, 'create'])
        ->name('companies.certificate.create')
        ->middleware('can:update,company');
    Route::post('companies/{company}/certificate', [CertificateController::class, 'store'])
        ->name('companies.certificate.store')
        ->middleware('can:update,company');

    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);

    // Inutilization
    Route::get('/nfe/inutilization', [InutilizationController::class, 'create'])->name('nfe.inutilization.create');
    Route::post('/nfe/inutilization', [InutilizationController::class, 'store'])->name('nfe.inutilization.store');

    Route::resource('nfe', NFeController::class);
    Route::post('nfe/{nfe}/transmit', [NFeController::class, 'transmit'])
        ->name('nfe.transmit')
        ->middleware('can:transmit,nfe');
    Route::get('nfe/{nfe}/pdf', [NFeController::class, 'downloadPdf'])
        ->name('nfe.pdf')
        ->middleware('can:view,nfe');
    Route::get('nfe/{nfe}/view', [NFeController::class, 'viewPdf'])
        ->name('nfe.view')
        ->middleware('can:view,nfe');
    Route::post('nfe/{nfe}/cancel', [NFeController::class, 'cancel'])
        ->name('nfe.cancel')
        ->middleware('can:cancel,nfe');
    Route::get('nfe/{nfe}/correction', [NFeController::class, 'correction'])
        ->name('nfe.correction')
        ->middleware('can:correction,nfe');
    Route::post('nfe/{nfe}/correction', [NFeController::class, 'storeCorrection'])
        ->name('nfe.correction.store')
        ->middleware('can:correction,nfe');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
