<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CancelNfeRequest
 * 
 * Handles validation for NFe cancellation.
 * 
 * @package App\Http\Requests
 */
class CancelNfeRequest extends FormRequest
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
            'justification' => 'required|string|min:15',
        ];
    }
}
