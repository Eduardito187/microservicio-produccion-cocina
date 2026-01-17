<?php

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\ListarCalendarios;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;

class ListarCalendariosHandler
{
    /**
     * @var CalendarioRepositoryInterface
     */
    public readonly CalendarioRepositoryInterface $calendarioRepository;

    /**
     * @var TransactionAggregate
     */
    private readonly TransactionAggregate $transactionAggregate;

    /**
     * Constructor
     *
     * @param CalendarioRepositoryInterface $calendarioRepository
     * @param TransactionAggregate $transactionAggregate
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
    }

    /**
     * @param ListarCalendarios $command
     * @return array
     */
    public function __invoke(ListarCalendarios $command): array
    {
        return $this->transactionAggregate->runTransaction(function (): array {
            return array_map([$this, 'mapCalendario'], $this->calendarioRepository->list());
        });
    }

    /**
     * @param Calendario $calendario
     * @return array
     */
    private function mapCalendario(Calendario $calendario): array
    {
        return [
            'id' => $calendario->id,
            'fecha' => $calendario->fecha->format('Y-m-d'),
            'sucursal_id' => $calendario->sucursalId,
        ];
    }
}








