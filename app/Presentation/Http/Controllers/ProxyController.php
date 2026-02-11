<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

/**
 * @class ProxyController
 * @package App\Presentation\Http\Controllers
 */
class ProxyController
{
    /**
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/users');
        return response()->json($response->json(), $response->status());
    }

    /**
     * @return JsonResponse
     */
    public function posts(): JsonResponse
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/posts');
        return response()->json($response->json(), $response->status());
    }
}
