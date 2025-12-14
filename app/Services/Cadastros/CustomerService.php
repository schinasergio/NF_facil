<?php

namespace App\Services\Cadastros;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Create a new customer with their address.
     */
    public function createCustomer(array $customerData, array $addressData): Customer
    {
        return DB::transaction(function () use ($customerData, $addressData) {
            $address = Address::create($addressData);

            $customerData['address_id'] = $address->id;
            return Customer::create($customerData);
        });
    }

    /**
     * Update existing customer and address.
     */
    public function updateCustomer(Customer $customer, array $customerData, array $addressData): Customer
    {
        return DB::transaction(function () use ($customer, $customerData, $addressData) {
            if ($customer->address) {
                $customer->address->update($addressData);
            } else {
                $address = Address::create($addressData);
                $customer->address_id = $address->id;
            }

            $customer->update($customerData);
            return $customer;
        });
    }
}
