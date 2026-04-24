<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Providers;

use App\Infrastructure\Tracing\NullExporter;
use App\Infrastructure\Tracing\SpanExporterInterface;
use App\Infrastructure\Tracing\Tracer;
use App\Infrastructure\Tracing\ZipkinExporter;
use Illuminate\Support\ServiceProvider;

/**
 * @class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registrar los servicios de la aplicacion.
     */
    public function register(): void
    {
        $this->app->singleton(SpanExporterInterface::class, function (): SpanExporterInterface {
            $enabled = (bool) config('tracing.enabled', false);
            $exporter = (string) config('tracing.exporter', 'zipkin');
            if (! $enabled || $exporter !== 'zipkin') {
                return new NullExporter;
            }
            $endpoint = (string) config('tracing.zipkin.endpoint', '');
            if ($endpoint === '') {
                return new NullExporter;
            }

            return new ZipkinExporter(
                $endpoint,
                (string) config('tracing.service_name', 'laravel'),
                (float) config('tracing.zipkin.timeout', 2.0),
            );
        });

        $this->app->singleton(Tracer::class, function ($app): Tracer {
            return new Tracer(
                (bool) config('tracing.enabled', false),
                $app->make(SpanExporterInterface::class),
            );
        });
    }

    /**
     * Inicializar los servicios de la aplicacion.
     */
    public function boot(): void
    {
        //
    }
}
