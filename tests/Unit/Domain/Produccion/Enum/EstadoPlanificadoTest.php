<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Enum;

use App\Domain\Produccion\Enum\EstadoPlanificado;
use PHPUnit\Framework\TestCase;

/**
 * @class EstadoPlanificadoTest
 * @package Tests\Unit\Domain\Produccion\Enum
 */
class EstadoPlanificadoTest extends TestCase
{
    /**
     * @return void
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('PROGRAMADO', EstadoPlanificado::PROGRAMADO->value);
        $this->assertSame('PROCESANDO', EstadoPlanificado::PROCESANDO->value);
        $this->assertSame('DESPACHADO', EstadoPlanificado::DESPACHADO->value);
    }

    /**
     * @return void
     */
    public function test_enum_from_value(): void
    {
        $this->assertSame(EstadoPlanificado::PROCESANDO, EstadoPlanificado::from('PROCESANDO'));
    }
}
