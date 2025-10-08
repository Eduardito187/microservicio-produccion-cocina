<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Domain\Produccion\Repository\OrdenProduccion;
use App\Application\Produccion\Command\GenerarOP;
use Illuminate\Support\Facades\DB;

class GenerarOPHandler
{
    /**
     * @var OrdenProduccion
     */
    private OrdenProduccion $repo;

    /**
     * Constructor
     * 
     * @param OrdenProduccion $repo
     */
    public function __construct(OrdenProduccion $repo) {
        $this->repo = $repo;
    }

    /**
     * @param GenerarOP $cmd
     * @return string
     */
    public function __invoke(GenerarOP $cmd): string
    {
        $opId = sprintf('OP-%s-%s', $cmd->fecha->format('Ymd'), $cmd->sucursalId);

        return DB::transaction(function () use ($cmd, $opId) {
            // 1) Cargar o crear agregado
            $op = $this->repo->byId($opId) ?? AggregateOrdenProduccion::crear($opId, $cmd->fecha, $cmd->sucursalId);

            // 2) (omitir: lógica para lotes/estaciones…)

            // 3) Persistir agregado(s)
            $this->repo->save($op);

            // 4) Guardar eventos en Outbox (AÚN no publicar)
            foreach ($op->pullEvents() as $event) {
                OutboxStore::append(
                    name: $event->name(),
                    aggregateId: $event->aggregateId(),
                    occurredOn: $event->occurredOn(),
                    payload: $event->toArray()
                );
            }

            return $opId;
        });
    }
}