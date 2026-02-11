<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\ValueObjects;

use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @class QtyTest
 * @package Tests\Unit\Domain\Produccion\ValueObjects
 */
class QtyTest extends TestCase
{
    /**
     * @return void
     */
    public function test_it_creates_a_valid_qty(): void
    {
        $qty = new Qty(5);
        $this->assertSame(5, $qty->value());
    }

    /**
     * @return void
     */
    public function test_it_throws_exception_when_value_is_not_positive(): void
    {
        $this->expectException(DomainException::class);
        new Qty(0);
    }

    /**
     * @return void
     */
    public function test_add_returns_new_instance_and_does_not_modify_originals(): void
    {
        $a = new Qty(3);
        $b = new Qty(2);
        $c = $a->add($b);
        $this->assertInstanceOf(Qty::class, $c);
        $this->assertSame(5, $c->value());
        $this->assertSame(3, $a->value());
        $this->assertSame(2, $b->value());
    }
}
