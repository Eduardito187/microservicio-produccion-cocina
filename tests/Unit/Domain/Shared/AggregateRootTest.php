<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Shared\Events\BaseDomainEvent;
use PHPUnit\Framework\TestCase;

/**
 * @class AggregateRootTest
 */
class AggregateRootTest extends TestCase
{
    public function test_record_and_pull_events_behaviour(): void
    {
        $aggregate = new class
        {
            use AggregateRoot;

            public function raise(): void
            {
                $this->record(new class(1) extends BaseDomainEvent
                {
                    public function toArray(): array
                    {
                        return ['x' => 1];
                    }
                });
            }
        };

        $this->assertSame([], $aggregate->pullEvents());

        $aggregate->raise();
        $events = $aggregate->pullEvents();

        $this->assertCount(1, $events);
        $this->assertSame(['x' => 1], $events[0]->toArray());
        $this->assertSame([], $aggregate->pullEvents());
    }
}
