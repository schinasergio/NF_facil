<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCustomerRequest
 * 
 * Handles validation for Customer creation and updates.
 * 
 * @package App\Http\Requests
 */
class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $customerId = $this->route('customer') ? $this->route('customer')->id : null;

        $uniqueRule = 'unique:customers,cpf_cnpj';
        if ($customerId) {
            $uniqueRule .= ',' . $customerId;
        }

        return [
            'razao_social' => 'required|string|max:255',
            'cpf_cnpj' => ['required', 'string', 'max:18', $uniqueRule],
            'email' => 'nullable|email',
            'indicador_ie' => 'required|string',

            // Address validation
            'logradouro' => 'required|string',
            'numero' => 'required|string',
            'complemento' => 'nullable|string',
            'bairro' => 'required|string',
            'cep' => 'required|string|max:10',
            'cidade' => 'required|string',
            'uf' => 'required|string|max:2',
            'pais' => 'nullable|string',
        ];
    }
}
