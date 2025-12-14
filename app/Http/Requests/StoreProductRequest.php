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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
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
