<?php

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\RegistrarInboundEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Produccion\Repository\InboundEventRepositoryInterface;

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
     * Constructor
     *
     * @param InboundEventRepositoryInterface $inboundEventRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        InboundEventRepositoryInterface $inboundEventRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->inboundEventRepository = $inboundEventRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param RegistrarInboundEvent $command
     * @return bool
     */
    public function __invoke(RegistrarInboundEvent $command): bool
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): bool {
            if ($this->inboundEventRepository->existsByEventId($command->eventId)) {
                return true;
            }

            $event = new InboundEvent(
                null,
                $command->eventId,
                $command->eventName,
                $command->occurredOn,
                $command->payload
            );

            $this->inboundEventRepository->save($event);

            return false;
        });
    }
}
