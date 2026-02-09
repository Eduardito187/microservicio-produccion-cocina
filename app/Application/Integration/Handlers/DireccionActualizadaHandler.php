<?php

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DireccionActualizadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DireccionActualizadaHandler implements IntegrationEventHandlerInterface
{
    public function __construct(
        private readonly DireccionRepositoryInterface $direccionRepository,
        private readonly TransactionAggregate $transactionAggregate
    ) {
    }

    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->direccionRepository->byId($event->id);
            } catch (ModelNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->linea1 === null) {
                logger()->warning('Direccion update ignored (missing linea1 for create)', [
                    'direccion_id' => $event->id,
                ]);
                return;
            }

            $direccion = $existing ?? new Direccion(
                $event->id,
                $event->nombre,
                $event->linea1 ?? '',
                $event->linea2,
                $event->ciudad,
                $event->provincia,
                $event->pais,
                $event->geo
            );

            if ($event->nombre !== null) {
                $direccion->nombre = $event->nombre;
            }
            if ($event->linea1 !== null) {
                $direccion->linea1 = $event->linea1;
            }
            if ($event->linea2 !== null) {
                $direccion->linea2 = $event->linea2;
            }
            if ($event->ciudad !== null) {
                $direccion->ciudad = $event->ciudad;
            }
            if ($event->provincia !== null) {
                $direccion->provincia = $event->provincia;
            }
            if ($event->pais !== null) {
                $direccion->pais = $event->pais;
            }
            if ($event->geo !== null) {
                $direccion->geo = $event->geo;
            }

            $this->direccionRepository->save($direccion);
        });
    }
}
