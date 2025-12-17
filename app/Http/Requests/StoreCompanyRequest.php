<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCompanyRequest
 * 
 * Handles validation for Company creation and updates.
 * 
 * @package App\Http\Requests
 */
class StoreCompanyRequest extends FormRequest
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
        $companyId = $this->route('company') ? $this->route('company')->id : null;

        $uniqueRule = 'unique:companies,cnpj';
        if ($companyId) {
            $uniqueRule .= ',' . $companyId;
        }

        return [
            'razao_social' => 'required|string|max:255',
            'cnpj' => ['required', 'string', 'max:18', $uniqueRule],
            'regime_tributario' => 'required|string',
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
    public function messages()
    {
        return [
            'razao_social.required' => 'Razão Social é obrigatória',
            'cnpj.required' => 'CNPJ é obrigatório',
            'cnpj.unique' => 'ESTE CNPJ JÁ ESTÁ EM USO (Hardcoded Check)',
            'regime_tributario.required' => 'Selecione o Regime Tributário',
            // Add other messages as needed
        ];
    }
}
