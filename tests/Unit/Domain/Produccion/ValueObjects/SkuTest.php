<?php

namespace Tests\Unit\Domain\Produccion\ValueObjects;

use App\Domain\Produccion\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;
use DomainException;

class SkuTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_it_normalizes_value_to_uppercase(): void
    {
        $sku = new Sku('abc-123');
        $this->assertSame('ABC-123', $sku->value());
    }

    /**
     * @inheritDoc
     */
    public function test_it_throws_exception_when_value_is_empty(): void
    {
        $this->expectException(DomainException::class);
        new Sku('');
    }
}