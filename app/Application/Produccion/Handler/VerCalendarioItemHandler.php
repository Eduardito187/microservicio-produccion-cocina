<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerCalendarioItem;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;

/**
 * @class VerCalendarioItemHandler
 */
class VerCalendarioItemHandler
{
    /**
     * @var CalendarioItemRepositoryInterface
     */
    private $calendarioItemRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(VerCalendarioItem $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $item = $this->calendarioItemRepository->byId($command->id);

            return $this->mapCalendarioItem($item);
        });
    }

    private function mapCalendarioItem(CalendarioItem $item): array
    {
        return [
            'id' => $item->id,
            'calendario_id' => $item->calendarioId,
            'item_despacho_id' => $item->itemDespachoId,
        ];
    }
}
