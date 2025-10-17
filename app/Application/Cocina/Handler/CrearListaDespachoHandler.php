<?php

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Infrastructure\Persistence\Eloquent\Outbox\OutboxStore;
use App\Domain\Produccion\Repository\OrdenProduccion;
use App\Application\Produccion\Command\GenerarOP;
use Illuminate\Support\Facades\DB;

class CrearListaDespachoHandler
{
    /**
     * @var OrdenProduccion
     */
    private OrdenProduccion $ordenProduccionRespository;

    /**
     * Constructor
     * 
     * @param OrdenProduccion $ordenProduccionRespository
     */
    public function __construct(OrdenProduccion $ordenProduccionRespository) {
        $this->ordenProduccionRespository = $ordenProduccionRespository;
    }

    /**
     * @param GenerarOP $command
     * @return string
     */
    public function __invoke(GenerarOP $command): string
    {
        $opId = sprintf('OP-%s-%s', $command->fecha->format('Ymd'), $command->sucursalId);

        return DB::transaction(function () use ($command, $opId) {
            $op = $this->ordenProduccionRespository->byId($opId) ?? AggregateOrdenProduccion::crear($opId, $command->fecha, $command->sucursalId);

            //estacion y logica de lotes

            $this->ordenProduccionRespository->save($op);

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