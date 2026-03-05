<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * @class Authenticate
 * @package App\Presentation\Http\Middleware
 */
class Authenticate extends Middleware
{
    /**
     * Obtener la ruta de redireccion cuando el usuario no esta autenticado.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
