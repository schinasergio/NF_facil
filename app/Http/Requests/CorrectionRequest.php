<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CorrectionRequest
 * 
 * Handles validation for CC-e requests.
 * 
 * @package App\Http\Requests
 */
class CorrectionRequest extends FormRequest
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
            'correction_text' => 'required|string|min:15|max:1000',
        ];
    }
}
