<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\ValueObjects;

use App\Domain\Produccion\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @class SkuTest
 * @package Tests\Unit\Domain\Produccion\ValueObjects
 */
class SkuTest extends TestCase
{
    /**
     * @return void
     */
    public function test_it_normalizes_value_to_uppercase(): void
    {
        $sku = new Sku('abc-123');
        $this->assertSame('ABC-123', $sku->value());
    }

    /**
     * @return void
     */
    public function test_it_throws_exception_when_value_is_empty(): void
    {
        $this->expectException(DomainException::class);
        new Sku('');
    }
}
