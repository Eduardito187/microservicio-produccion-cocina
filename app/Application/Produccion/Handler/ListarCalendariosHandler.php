<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarCalendarios;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;

/**
 * @class ListarCalendariosHandler
 */
class ListarCalendariosHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    private $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * Constructor
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    public function __invoke(ListarCalendarios $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapCalendario'], $this->calendarioRepository->list());
        });
    }

    private function mapCalendario(Calendario $calendario): array
    {
        return [
            'id' => $calendario->id,
            'fecha' => $calendario->fecha->format('Y-m-d'),
        ];
    }
}
