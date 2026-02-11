<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @class ValueObjectsTest
 * @package Tests\Unit\Domain\Produccion
 */
class ValueObjectsTest extends TestCase
{
    /**
     * @return void
     */
    public function test_sku_normaliza_a_mayusculas_y_trim(): void
    {
        $sku = new Sku('  abc-123  ');
        $this->assertSame('ABC-123', $sku->value());
    }

    /**
     * @return void
     */
    public function test_sku_vacio_lanza_excepcion(): void
    {
        $this->expectException(DomainException::class);
        new Sku('   ');
    }

    /**
     * @return void
     */
    public function test_qty_rechaza_cero_o_negativo(): void
    {
        $this->expectException(DomainException::class);
        new Qty(0);
    }

    /**
     * @return void
     */
    public function test_qty_acepta_positivo(): void
    {
        $qty = new Qty(3);
        $this->assertSame(3, $qty->value);
    }
}
