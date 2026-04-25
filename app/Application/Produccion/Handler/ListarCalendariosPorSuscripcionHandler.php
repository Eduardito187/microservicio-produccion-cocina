<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarCalendariosPorSuscripcion;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;

/**
 * @class ListarCalendariosPorSuscripcionHandler
 */
class ListarCalendariosPorSuscripcionHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var SuscripcionRepositoryInterface
     */
    private $suscripcionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        SuscripcionRepositoryInterface $suscripcionRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->suscripcionRepository = $suscripcionRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarCalendariosPorSuscripcion $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $this->suscripcionRepository->byId($command->suscripcionId);

            return array_map(
                [$this, 'mapCalendario'],
                $this->calendarioRepository->bySuscripcionId($command->suscripcionId)
            );
        });
    }

    private function mapCalendario(Calendario $calendario): array
    {
        return [
            'id' => $calendario->id,
            'fecha' => $calendario->fecha->format('Y-m-d'),
            'entrega_id' => $calendario->entregaId,
            'contrato_id' => $calendario->contratoId,
            'estado' => $calendario->estado,
        ];
    }
}
