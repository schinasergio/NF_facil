<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Report Routes
Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export');

Route::resource('companies', \App\Http\Controllers\CompanyController::class); // Middleware can handled here or via policy auto-discovery, but resource doesn't auto apply 'can'.
// We need to apply middleware to specific routes or Resource. 
// For Laravel Resource Controller + Policy, we usually use ->middleware('can:view,company') in route definition BUT for resource it is tricky.
// Better to Use Controller -> authorize() method inside methods for simplicity in Laravel 11 if Base Controller doesn't have middleware method.
// Or just let Resource routes rely on internal checks.
// WAIT. If Controller doesn't have middleware method, we MUST use route middleware.

// Let's wrap them or add explicit middleware.
Route::resource('companies', \App\Http\Controllers\CompanyController::class);

Route::get('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'create'])
    ->name('companies.certificate.create')
    ->middleware('can:update,company');
Route::post('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'store'])
    ->name('companies.certificate.store')
    ->middleware('can:update,company');

Route::resource('customers', \App\Http\Controllers\CustomerController::class);
Route::resource('products', \App\Http\Controllers\ProductController::class);

// Inutilization
Route::get('/nfe/inutilization', [\App\Http\Controllers\InutilizationController::class, 'create'])->name('nfe.inutilization.create');
Route::post('/nfe/inutilization', [\App\Http\Controllers\InutilizationController::class, 'store'])->name('nfe.inutilization.store');

Route::resource('nfe', \App\Http\Controllers\NFeController::class);
Route::post('nfe/{nfe}/transmit', [\App\Http\Controllers\NFeController::class, 'transmit'])
    ->name('nfe.transmit')
    ->middleware('can:transmit,nfe');
Route::get('nfe/{nfe}/pdf', [\App\Http\Controllers\NFeController::class, 'downloadPdf'])
    ->name('nfe.pdf')
    ->middleware('can:view,nfe');
Route::get('nfe/{nfe}/view', [\App\Http\Controllers\NFeController::class, 'viewPdf'])
    ->name('nfe.view')
    ->middleware('can:view,nfe');
Route::post('nfe/{nfe}/cancel', [\App\Http\Controllers\NFeController::class, 'cancel'])
    ->name('nfe.cancel')
    ->middleware('can:cancel,nfe');
Route::get('nfe/{nfe}/correction', [\App\Http\Controllers\NFeController::class, 'correction'])
    ->name('nfe.correction')
    ->middleware('can:correction,nfe');
Route::post('nfe/{nfe}/correction', [\App\Http\Controllers\NFeController::class, 'storeCorrection'])
    ->name('nfe.correction.store')
    ->middleware('can:correction,nfe');

