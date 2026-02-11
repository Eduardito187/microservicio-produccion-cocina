<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @class GenerarOPRequest
 * @package App\Presentation\Http\Requests
 */
class GenerarOPRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'sucursalId' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['bail', 'required', 'string', 'regex:/\\S/'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
