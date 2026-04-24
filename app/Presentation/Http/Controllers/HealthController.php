<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'up';
        $httpStatus = 200;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'down';
            $httpStatus = 503;
        }

        $overall = $dbStatus === 'up' ? 'ok' : 'degraded';

        return new JsonResponse([
            'status' => $overall,
            'service' => config('service-discovery.service_name'),
            'checks' => [
                'database' => $dbStatus,
            ],
            'time' => gmdate('c'),
        ], $httpStatus);
    }
}
