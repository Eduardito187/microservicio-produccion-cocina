<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Application\Produccion\Command\VerCalendario;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;

/**
 * @class VerCalendarioHandler
 */
class VerCalendarioHandler
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

    public function __invoke(VerCalendario $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $calendario = $this->calendarioRepository->byId($command->id);

            return $this->mapCalendario($calendario);
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
