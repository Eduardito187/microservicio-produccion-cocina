<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

final class NullExporter implements SpanExporterInterface
{
    public function export(array $spans): void
    {
        // intentional no-op
    }
}
