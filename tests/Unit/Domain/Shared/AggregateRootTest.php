<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Shared\Events\BaseDomainEvent;
use PHPUnit\Framework\TestCase;

class AggregateRootTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_record_and_pull_events_behaviour(): void
    {
        $agg = new class {
            use AggregateRoot;

            public function raise(): void
            {
                $this->record(new class(1) extends BaseDomainEvent {
                    public function toArray(): array { return ['x' => 1]; }
                });
            }
        };

        $this->assertSame([], $agg->pullEvents());

        $agg->raise();
        $events = $agg->pullEvents();

        $this->assertCount(1, $events);
        $this->assertSame(['x' => 1], $events[0]->toArray());
        $this->assertSame([], $agg->pullEvents());
    }
}
