<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\Events\BaseDomainEvent;
use App\Domain\Shared\ValueObjects\ValueObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @class SharedKernelTest
 */
class SharedKernelTest extends TestCase
{
    public function test_base_domain_event_defaults_and_accessors(): void
    {
        $fixed = new DateTimeImmutable('2025-11-04 10:00:00');
        $event = new BaseDomainEvent(99, $fixed);

        $this->assertSame(BaseDomainEvent::class, $event->name());
        $this->assertSame(99, $event->aggregateId());
        $this->assertSame($fixed, $event->occurredOn());
        $this->assertSame([], $event->toArray());

        $event2 = new BaseDomainEvent(null);
        $this->assertInstanceOf(DateTimeImmutable::class, $event2->occurredOn());
    }

    public function test_value_object_equals_uses_state_equality(): void
    {
        $value = new class(1) extends ValueObject
        {
            /**
             * Constructor
             */
            public function __construct(public int $aux) {}
        };

        $this->assertTrue($value->equals($value));
    }
}
