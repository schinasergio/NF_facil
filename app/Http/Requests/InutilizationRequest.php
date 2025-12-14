<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class InutilizationRequest
 * 
 * Handles validation for Number Voiding (Inutilização) requests.
 * 
 * @package App\Http\Requests
 */
class InutilizationRequest extends FormRequest
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
            'serie' => 'required|integer',
            'numero_inicial' => 'required|integer',
            'numero_final' => 'required|integer|gte:numero_inicial',
            'justificativa' => 'required|string|min:15',
        ];
    }
}
