<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * @class ProxyController
 * @package App\Presentation\Http\Controllers
 */
class ProxyController
{
    /**
     * @return RedirectResponse
     */
    public function users(): RedirectResponse
    {
        return redirect()->away('https://jsonplaceholder.typicode.com/users');
    }

    /**
     * @return RedirectResponse
     */
    public function posts(): RedirectResponse
    {
        return redirect()->away('https://jsonplaceholder.typicode.com/posts');
    }
}
