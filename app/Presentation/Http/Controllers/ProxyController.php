<?php

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ProxyController
{
    public function users(): JsonResponse
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/users');
        return response()->json($response->json(), $response->status());
    }

    public function posts(): JsonResponse
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/posts');
        return response()->json($response->json(), $response->status());
    }
}