<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

interface SpanExporterInterface
{
    /**
     * @param  Span[]  $spans
     */
    public function export(array $spans): void;
}
