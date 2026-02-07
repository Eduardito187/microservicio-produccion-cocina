<?php

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;
use Illuminate\Database\QueryException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RegistrarInboundEventHandler
{
    /**
     * @var InboundEventRepositoryInterface
     */
    public readonly InboundEventRepositoryInterface $inboundEventRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param InboundEventRepositoryInterface $inboundEventRepository
     * @param TransactionAggregate $transactionAggregate
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        InboundEventRepositoryInterface $inboundEventRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->inboundEventRepository = $inboundEventRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param RegistrarInboundEvent $command
     * @return bool
     */
    public function __invoke(RegistrarInboundEvent $command): bool
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): bool {
            $event = new InboundEvent(
                null,
                $command->eventId,
                $command->eventName,
                $command->occurredOn,
                $command->payload
            );

            try {
                $this->inboundEventRepository->save($event);
            } catch (QueryException $e) {
                if ($this->isDuplicateKey($e)) {
                    $this->logger->info('Inbound event duplicate', [
                        'event_id' => $command->eventId,
                        'event_name' => $command->eventName,
                    ]);
                    return true;
                }
                $this->logger->error('Inbound event insert failed', [
                    'event_id' => $command->eventId,
                    'event_name' => $command->eventName,
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
                throw $e;
            }

            return false;
        });
    }

    /**
     * @param QueryException $e
     * @return bool
     */
    private function isDuplicateKey(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo ?? null;
        if (!is_array($errorInfo) || !isset($errorInfo[1]) || (int) $errorInfo[1] !== 1062) {
            return false;
        }

        $message = $e->getMessage();
        if (!is_string($message)) {
            return false;
        }

        return str_contains($message, 'inbound_events_event_id_unique')
            || str_contains($message, 'event_id');
    }
}
