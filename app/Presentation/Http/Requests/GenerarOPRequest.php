<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @class GenerarOPRequest
 */
class GenerarOPRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['bail', 'required', 'string', 'regex:/\\S/'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
