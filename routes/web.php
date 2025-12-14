<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('companies', \App\Http\Controllers\CompanyController::class);
Route::resource('customers', \App\Http\Controllers\CustomerController::class);
