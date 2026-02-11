<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Produccion\Command\VerCalendario;
use App\Domain\Produccion\Entity\Calendario;

/**
 * @class VerCalendarioHandler
 * @package App\Application\Produccion\Handler
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
     * @param VerCalendario $command
     * @return array
     */
    public function __invoke(VerCalendario $command): array
    {
        return $this->transactionAggregate->runTransaction(function () use ($command): array {
            $calendario = $this->calendarioRepository->byId($command->id);
            return $this->mapCalendario($calendario);
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
