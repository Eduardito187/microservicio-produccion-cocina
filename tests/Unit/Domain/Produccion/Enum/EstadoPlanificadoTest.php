<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Enum;

use App\Domain\Produccion\Enum\EstadoPlanificado;
use PHPUnit\Framework\TestCase;

/**
 * @class EstadoPlanificadoTest
 */
class EstadoPlanificadoTest extends TestCase
{
    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('PROGRAMADO', EstadoPlanificado::PROGRAMADO->value);
        $this->assertSame('PROCESANDO', EstadoPlanificado::PROCESANDO->value);
        $this->assertSame('DESPACHADO', EstadoPlanificado::DESPACHADO->value);
    }

    public function test_enum_from_value(): void
    {
        $this->assertSame(EstadoPlanificado::PROCESANDO, EstadoPlanificado::from('PROCESANDO'));
    }
}
