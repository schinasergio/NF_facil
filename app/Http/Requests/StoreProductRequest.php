<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreProductRequest
 * 
 * Handles validation for Product creation and updates.
 * 
 * @package App\Http\Requests
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        file_put_contents('/tmp/request_debug.log', "Request Authorize Reached\n", FILE_APPEND);
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'cest' => preg_replace('/[^0-9]/', '', $this->cest),
            'ncm' => preg_replace('/[^0-9]/', '', $this->ncm),
            'preco_venda' => str_replace(',', '.', str_replace('.', '', $this->preco_venda)), // PT-BR format fix
        ]);

        // Debug
        // file_put_contents('/tmp/request_debug.log', "Sanitized CEST: " . $this->cest . "\n", FILE_APPEND);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        file_put_contents('/tmp/request_debug.log', "Request Rules Reached\n", FILE_APPEND);
        return [
            'nome' => 'required|string|max:255',
            'codigo_sku' => 'nullable|string|max:255',
            'ncm' => 'required|string|size:8',
            'cest' => 'nullable|string|max:7',
            'unidade' => 'required|string|max:10',
            'preco_venda' => 'required|numeric|min:0',
            'origem' => 'required|integer',
        ];
    }
}
