<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * @class ProxyController
 */
class ProxyController
{
    public function users(): RedirectResponse
    {
        return redirect()->away('https://jsonplaceholder.typicode.com/users');
    }

    public function posts(): RedirectResponse
    {
        return redirect()->away('https://jsonplaceholder.typicode.com/posts');
    }
}
