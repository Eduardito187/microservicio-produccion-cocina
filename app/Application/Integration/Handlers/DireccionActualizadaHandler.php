<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\DireccionActualizadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Produccion\Entity\Direccion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class DireccionActualizadaHandler
 * @package App\Application\Integration\Handlers
 */
class DireccionActualizadaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var DireccionRepositoryInterface
     */
    private $direccionRepository;

    /**
     * @var TransactionAggregate
     */
    private $transactionAggregate;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param DireccionRepositoryInterface $direccionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        DireccionRepositoryInterface $direccionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->direccionRepository = $direccionRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = DireccionActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->direccionRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->linea1 === null) {
                $this->logger->warning('Direccion update ignored (missing linea1 for create)', [
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
