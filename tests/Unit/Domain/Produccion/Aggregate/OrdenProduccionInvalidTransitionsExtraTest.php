<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoOP;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @class OrdenProduccionInvalidTransitionsExtraTest
 */
class OrdenProduccionInvalidTransitionsExtraTest extends TestCase
{
    public function test_no_permite_planificar_si_no_esta_creada(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(
            1, new DateTimeImmutable('2025-11-04'), EstadoOP::PLANIFICADA, [], [], []
        );
        $this->expectException(DomainException::class);
        $ordenProduccion->planificar();
    }

    public function test_no_permite_procesar_si_no_esta_planificada(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(
            1, new DateTimeImmutable('2025-11-04'), EstadoOP::CREADA, [], [], []
        );
        $this->expectException(DomainException::class);
        $ordenProduccion->procesar();
    }

    public function test_no_permite_cerrar_si_no_esta_en_proceso(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(
            1, new DateTimeImmutable('2025-11-04'), EstadoOP::PLANIFICADA, [], [], []
        );
        $this->expectException(DomainException::class);
        $ordenProduccion->cerrar();
    }
}
