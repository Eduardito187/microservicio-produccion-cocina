<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Domain\Shared\Aggregate\AggregateRoot;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Mockery;

class AggregateRootPublishOutboxTest extends TestCase
{
    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_publish_outbox_appends_all_events_and_clears_them(): void
    {
        $outbox = Mockery::mock('alias:App\Infrastructure\Persistence\Outbox\OutboxStore');
        $aggregate = new class {
            use AggregateRoot;

            public function addEvent(DomainEventInterface $event): void
            {
                $this->record($event);
            }
        };

        $t1 = new DateTimeImmutable('2025-01-01 10:00:00');
        $t2 = new DateTimeImmutable('2025-01-01 11:00:00');

        $event1 = new class($t1) implements DomainEventInterface {
            public function __construct(private DateTimeImmutable $t) {}

            public function name(): string {
                return 'E1';
            }

            public function occurredOn(): DateTimeImmutable {
                return $this->t;
            }

            public function aggregateId(): string|int|null {
                return null;
            }

            public function toArray(): array {
                return ['k' => 'v1'];
            }
        };

        $event2 = new class($t2) implements DomainEventInterface {
            public function __construct(private DateTimeImmutable $t) {}
            public function name(): string { return 'E2'; }
            public function occurredOn(): DateTimeImmutable { return $this->t; }
            public function aggregateId(): string|int|null { return null; }
            public function toArray(): array { return ['k' => 'v2']; }
        };

        $aggregate->addEvent($event1);
        $aggregate->addEvent($event2);
        $outbox->shouldReceive('append')->once()
            ->withArgs(function ($name, $aggregateregateId, $occurredOn, $payload) use ($t1) {
                return $name === 'E1' && $aggregateregateId === 123 && $occurredOn == $t1 && $payload === ['k' => 'v1'];
            });
        $outbox->shouldReceive('append')->once()
            ->withArgs(function ($name, $aggregateregateId, $occurredOn, $payload) use ($t2) {
                return $name === 'E2' && $aggregateregateId === 123 && $occurredOn == $t2 && $payload === ['k' => 'v2'];
            });
        $aggregate->publishOutbox(123);
        $this->assertCount(0, $aggregate->pullEvents());
    }
}