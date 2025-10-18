<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Application\Produccion\Command\ProcesadorOP;
use Illuminate\Support\Facades\DB;

class ProcesadorOPHandler
{
    /**
     * @var OrdenProduccionRepositoryInterface
     */
    public readonly OrdenProduccionRepositoryInterface $ordenProduccionRepository;

    /**
     * @var ProduccionBatchRepositoryInterface
     */
    public readonly ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface;

    /**
     * Constructor
     * 
     * @param OrdenProduccionRepositoryInterface $ordenProduccionRepository
     * @param ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
     */
    public function __construct(
        OrdenProduccionRepositoryInterface $ordenProduccionRepository,
        ProduccionBatchRepositoryInterface $produccionBatchRepositoryInterface
    ) {
        $this->ordenProduccionRepository = $ordenProduccionRepository;
        $this->produccionBatchRepositoryInterface = $produccionBatchRepositoryInterface;
    }

    /**
     * @param ProcesadorOP $command
     * @return string|int|null
     */
    public function __invoke(ProcesadorOP $command): string|int|null
    {
        $ordenProduccionId = DB::transaction(function () use ($command): int {
            $ordenProduccion = $command->opId ? $this->ordenProduccionRepository->byId($command->opId) : null;

            if ($ordenProduccion == null) {
                //no existe la orden
            }

            $ordenProduccion->procesar();
            $persistedId = $this->ordenProduccionRepository->save($ordenProduccion, false);

            foreach ($this->produccionBatchRepositoryInterface->byOrderId($command->opId) as $item) {
                $item->procesar();
                $this->produccionBatchRepositoryInterface->save($item);
            }

            foreach ($ordenProduccion->pullEvents() as $event) {
                OutboxStore::append(
                    name: $event->name(),
                    aggregateId: $persistedId,
                    occurredOn: $event->occurredOn(),
                    payload: $event->toArray()
                );
            }

            return $persistedId;
        });

        return $ordenProduccionId;
    }
}