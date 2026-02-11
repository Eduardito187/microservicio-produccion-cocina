<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerCalendarioItem;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @class VerCalendarioItemHandler
 * @package App\Application\Produccion\Handler
 */
class VerCalendarioItemHandler
{
    /**
     * @var CalendarioItemRepositoryInterface
     */
    private CalendarioItemRepositoryInterface $calendarioItemRepository;

    /**
     * @var TransactionAggregate
     */
    private TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param CalendarioItemRepositoryInterface $calendarioItemRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param VerCalendarioItem $command
     * @return array
     */
    public function __invoke(VerCalendarioItem $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $item = $this->calendarioItemRepository->byId($command->id);
            return $this->mapCalendarioItem($item);
        });
    }

    /**
     * @param CalendarioItem $item
     * @return array
     */
    private function mapCalendarioItem(CalendarioItem $item): array
    {
        return [
            'id' => $item->id,
            'calendario_id' => $item->calendarioId,
            'item_despacho_id' => $item->itemDespachoId,
        ];
    }
}
