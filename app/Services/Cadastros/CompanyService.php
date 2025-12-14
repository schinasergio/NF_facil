<?php

namespace App\Services\Cadastros;

use App\Models\Address;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Create a new company with its address.
     */
    public function createCompany(array $companyData, array $addressData): Company
    {
        return DB::transaction(function () use ($companyData, $addressData) {
            $address = Address::create($addressData);

            $companyData['address_id'] = $address->id;
            return Company::create($companyData);
        });
    }

    /**
     * Update existing company and address.
     */
    public function updateCompany(Company $company, array $companyData, array $addressData): Company
    {
        return DB::transaction(function () use ($company, $companyData, $addressData) {
            $company->address->update($addressData);
            $company->update($companyData);
            return $company;
        });
    }
}
