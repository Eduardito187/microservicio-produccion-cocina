<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Repository\ProduccionBatchRepositoryInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Domain\Produccion\Aggregate\EstadoPlanificado;
use App\Application\Produccion\Command\PlanificarOP;
use Illuminate\Support\Facades\DB;

class PlanificadorOPHandler
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
     * @param PlanificarOP $command
     * @return string|int|null
     */
    public function __invoke(PlanificarOP $command): string|int|null
    {
        $ordenProduccionId = DB::transaction(function () use ($command): int {
            $ordenProduccion = $command->opId ? $this->ordenProduccionRepository->byId($command->opId) : null;

            if ($ordenProduccion == null) {
                //no existe la orden
            }

            $ordenProduccion->planificar();
            $persistedId = $this->ordenProduccionRepository->save($ordenProduccion, false);

            $i = 1;
            foreach ($ordenProduccion->items() as $item) {
                $this->produccionBatchRepositoryInterface->save(
                    new AggregateProduccionBatch(
                        null,
                        $command->opId,
                        1,
                        1,
                        1,
                        1,
                        1,
                        50,
                        EstadoPlanificado::PROGRAMADO,
                        $item->sku,
                        $item->qty,
                        $i,
                        []
                    )
                );
                $i++;
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