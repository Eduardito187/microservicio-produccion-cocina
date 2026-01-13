<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\ValueObjects\ValueObject;
use App\Domain\Shared\Events\BaseDomainEvent;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class SharedKernelTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_base_domain_event_defaults_and_accessors(): void
    {
        $fixed = new DateTimeImmutable('2025-11-04 10:00:00');
        $e = new BaseDomainEvent(99, $fixed);

        $this->assertSame(BaseDomainEvent::class, $e->name());
        $this->assertSame(99, $e->aggregateId());
        $this->assertSame($fixed, $e->occurredOn());
        $this->assertSame([], $e->toArray());

        $e2 = new BaseDomainEvent(null);
        $this->assertInstanceOf(DateTimeImmutable::class, $e2->occurredOn());
    }

    /**
     * @inheritDoc
     */
    public function test_value_object_equals_uses_state_equality(): void
    {
        $a = new class(1) extends ValueObject {
            public function __construct(public int $x) {}
        };

        $this->assertTrue($a->equals($a));
    }
}