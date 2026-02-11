<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Shared\OutboxStoreInterface;
use App\Infrastructure\Jobs\PublishOutbox;

/**
 * @class OutboxEventPublisher
 * @package App\Infrastructure\Persistence\Outbox
 */
class OutboxEventPublisher implements DomainEventPublisherInterface
{
    /**
     * @var OutboxStoreInterface
     */
    private $outboxStore;

    /**
     * @var TransactionManagerInterface
     */
    private $transactionManager;

    /**
     * Constructor
     *
     * @param OutboxStoreInterface $outboxStore
     * @param TransactionManagerInterface $transactionManager
     */
    public function __construct(OutboxStoreInterface $outboxStore, TransactionManagerInterface $transactionManager)
    {
        $this->outboxStore = $outboxStore;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param DomainEventInterface[] $events
     * @param mixed $aggregateId
     * @return void
     */
    public function publish(array $events, mixed $aggregateId): void
    {
        if ($events === []) {
            return;
        }

        foreach ($events as $event) {
            $payload = $event->toArray();

            $this->outboxStore->append(
                $event->name(),
                $aggregateId ?? null,
                $event->occurredOn(),
                $payload
            );
        }

        if ((bool) env('OUTBOX_SKIP_DISPATCH', false) || app()->runningUnitTests()) {
            return;
        }

        $this->transactionManager->afterCommit(function (): void {
            PublishOutbox::dispatch();
        });
    }
}
