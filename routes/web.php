<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('companies', \App\Http\Controllers\CompanyController::class);
Route::get('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'create'])->name('companies.certificate.create');
Route::post('companies/{company}/certificate', [\App\Http\Controllers\CertificateController::class, 'store'])->name('companies.certificate.store');
Route::resource('customers', \App\Http\Controllers\CustomerController::class);
Route::resource('products', \App\Http\Controllers\ProductController::class);
Route::resource('nfe', \App\Http\Controllers\NFeController::class);
Route::post('nfe/{nfe}/transmit', [\App\Http\Controllers\NFeController::class, 'transmit'])->name('nfe.transmit');
