<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\ValueObjects\Capacidad;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @class CapacidadTest
 * @package Tests\Unit\Domain\Produccion
 */
class CapacidadTest extends TestCase
{
    /**
     * @return void
     */
    public function test_it_creates_a_valid_capacidad(): void
    {
        $c = new Capacidad(5);
        $this->assertSame(5, $c->value());
    }

    /**
     * @return void
     */
    public function test_it_throws_when_value_is_not_positive(): void
    {
        $this->expectException(DomainException::class);
        new Capacidad(0);
    }
}
