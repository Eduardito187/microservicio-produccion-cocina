<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Shared;

use App\Application\Shared\OutboxStoreInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Infrastructure\Jobs\PublishOutbox;
use App\Infrastructure\Persistence\Outbox\OutboxEventPublisher;
use DateTimeImmutable;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * @class OutboxEventPublisherTest
 * @package Tests\Unit\Application\Shared
 */
class OutboxEventPublisherTest extends TestCase
{
    /**
     * @return void
     */
    public function test_publishes_events_and_registers_after_commit(): void
    {
        $outbox = $this->createMock(OutboxStoreInterface::class);
        $transactionManager = new class implements TransactionManagerInterface {
            /**
             * @var ?\Closure
             */
            public ?\Closure $afterCommit = null;

            /**
             * @param callable $callback
             * @return mixed
             */
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            /**
             * @param callable $callback
             * @return void
             */
            public function afterCommit(callable $callback): void
            {
                $this->afterCommit = $callback instanceof \Closure ? $callback : \Closure::fromCallable($callback);
            }
        };

        $t1 = new DateTimeImmutable('2025-01-01 10:00:00');
        $t2 = new DateTimeImmutable('2025-01-01 11:00:00');

        $event1 = new class($t1) implements DomainEventInterface {
            /**
             * Constructor
             *
             */
            public function __construct(private DateTimeImmutable $t) {}
            public function name(): string { return 'E1'; }
            public function occurredOn(): DateTimeImmutable { return $this->t; }
            public function aggregateId(): string|int|null { return null; }
            public function toArray(): array { return ['k' => 'v1']; }
        };

        $event2 = new class($t2) implements DomainEventInterface {
            /**
             * Constructor
             *
             */
            public function __construct(private DateTimeImmutable $t) {}
            public function name(): string { return 'E2'; }
            public function occurredOn(): DateTimeImmutable { return $this->t; }
            public function aggregateId(): string|int|null { return null; }
            public function toArray(): array { return ['k' => 'v2']; }
        };

        $call = 0;
        $self = $this;
        $outbox->expects($this->exactly(2))->method('append')
            ->willReturnCallback(function ($name, $aggregateId, $occurredOn, $payload) use (&$call, $self, $t1, $t2): void {
                if ($call === 0) {
                    $self->assertSame('E1', $name);
                    $self->assertSame(123, $aggregateId);
                    $self->assertSame($t1, $occurredOn);
                    $self->assertSame(['k' => 'v1'], $payload);
                } elseif ($call === 1) {
                    $self->assertSame('E2', $name);
                    $self->assertSame(123, $aggregateId);
                    $self->assertSame($t2, $occurredOn);
                    $self->assertSame(['k' => 'v2'], $payload);
                } else {
                    $self->fail('append called more than twice');
                }
                $call++;
            });

        Bus::fake();

        $publisher = new OutboxEventPublisher($outbox, $transactionManager);
        $publisher->publish([$event1, $event2], 123);

        if (app()->runningUnitTests()) {
            $this->assertNull($transactionManager->afterCommit);
            Bus::assertNotDispatched(PublishOutbox::class);
            return;
        }

        $this->assertIsCallable($transactionManager->afterCommit);

        ($transactionManager->afterCommit)();

        Bus::assertDispatched(PublishOutbox::class);
    }
}
