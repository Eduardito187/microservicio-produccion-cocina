<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Outbox;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Shared\Events\Interface\DomainEventInterface;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Shared\OutboxUnitOfWorkInterface;
use App\Infrastructure\Jobs\PublishOutbox;

/**
 * @class OutboxEventPublisher
 * @package App\Infrastructure\Persistence\Outbox
 */
class OutboxEventPublisher implements DomainEventPublisherInterface
{
    /**
     * @var OutboxUnitOfWorkInterface
     */
    private $outboxUnitOfWork;

    /**
     * @var TransactionManagerInterface
     */
    private $transactionManager;

    /**
     * Constructor
     *
     * @param OutboxUnitOfWorkInterface $outboxUnitOfWork
     * @param TransactionManagerInterface $transactionManager
     */
    public function __construct(OutboxUnitOfWorkInterface $outboxUnitOfWork, TransactionManagerInterface $transactionManager)
    {
        $this->outboxUnitOfWork = $outboxUnitOfWork;
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

        $this->outboxUnitOfWork->register($events, $aggregateId);

        if ((bool) env('OUTBOX_SKIP_DISPATCH', false) || app()->runningUnitTests()) {
            return;
        }

        $this->transactionManager->afterCommit(function (): void {
            $dispatchSync = (bool) env('OUTBOX_DISPATCH_SYNC', true);

            if ($dispatchSync) {
                PublishOutbox::dispatchSync();
                return;
            }

            PublishOutbox::dispatch();
        });
    }
}
