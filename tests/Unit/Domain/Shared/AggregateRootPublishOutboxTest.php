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
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @inheritDoc
     */
    public function test_publish_outbox_appends_all_events_and_clears_them(): void
    {
        $outbox = Mockery::mock('alias:App\Infrastructure\Persistence\Outbox\OutboxStore');
        $agg = new class {
            use AggregateRoot;

            public function addEvent(DomainEventInterface $e): void
            {
                $this->record($e);
            }
        };

        $t1 = new DateTimeImmutable('2025-01-01 10:00:00');
        $t2 = new DateTimeImmutable('2025-01-01 11:00:00');

        $e1 = new class($t1) implements DomainEventInterface {
            public function __construct(private DateTimeImmutable $t) {}
            public function name(): string { return 'E1'; }
            public function occurredOn(): DateTimeImmutable { return $this->t; }
            public function aggregateId(): string|int|null { return null; }
            public function toArray(): array { return ['k' => 'v1']; }
        };

        $e2 = new class($t2) implements DomainEventInterface {
            public function __construct(private DateTimeImmutable $t) {}
            public function name(): string { return 'E2'; }
            public function occurredOn(): DateTimeImmutable { return $this->t; }
            public function aggregateId(): string|int|null { return null; }
            public function toArray(): array { return ['k' => 'v2']; }
        };

        $agg->addEvent($e1);
        $agg->addEvent($e2);

        $outbox->shouldReceive('append')->once()
        ->withArgs(function ($name, $aggregateId, $occurredOn, $payload) use ($t1) {
            return $name === 'E1' && $aggregateId === 123 && $occurredOn == $t1 && $payload === ['k' => 'v1'];
        });

        $outbox->shouldReceive('append')->once()
        ->withArgs(function ($name, $aggregateId, $occurredOn, $payload) use ($t2) {
            return $name === 'E2' && $aggregateId === 123 && $occurredOn == $t2 && $payload === ['k' => 'v2'];
        });

        $agg->publishOutbox(123);
        $this->assertCount(0, $agg->pullEvents());
    }
}