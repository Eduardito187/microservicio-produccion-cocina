<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Application\Produccion\Command\ListarCalendarioItems;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;

/**
 * @class ListarCalendarioItemsHandler
 * @package App\Application\Produccion\Handler
 */
class ListarCalendarioItemsHandler
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
     * @param ListarCalendarioItems $command
     * @return array
     */
    public function __invoke(ListarCalendarioItems $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapCalendarioItem'], $this->calendarioItemRepository->list());
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
