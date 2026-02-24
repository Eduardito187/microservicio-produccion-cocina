<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Integration\Events\RecetaActualizadaEvent;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Domain\Produccion\Entity\RecetaVersion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class RecetaActualizadaHandler
 * @package App\Application\Integration\Handlers
 */
class RecetaActualizadaHandler implements IntegrationEventHandlerInterface
{
    /**
     * @var RecetaVersionRepositoryInterface
     */
    private $recetaVersionRepository;

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
     * @param RecetaVersionRepositoryInterface $recetaVersionRepository
     * @param TransactionAggregate $transactionAggregate
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        RecetaVersionRepositoryInterface $recetaVersionRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->recetaVersionRepository = $recetaVersionRepository;
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
        $event = RecetaActualizadaEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $existing = null;
            try {
                $existing = $this->recetaVersionRepository->byId($event->id);
            } catch (EntityNotFoundException $e) {
                $existing = null;
            }

            if ($existing === null && $event->nombre === null) {
                $this->logger->warning('Receta update ignored (missing nombre for create)', [
                    'receta_id' => $event->id,
                ]);
                return;
            }

            $receta = $existing ?? new RecetaVersion(
                $event->id,
                $event->nombre ?? '',
                $event->nutrientes,
                $event->ingredientes,
                $event->description,
                $event->instructions,
                $event->totalCalories
            );

            if ($event->nombre !== null) {
                $receta->nombre = $event->nombre;
            }
            if ($event->nutrientes !== null) {
                $receta->nutrientes = $event->nutrientes;
            }
            if ($event->ingredientes !== null) {
                $receta->ingredientes = $event->ingredientes;
            }
            if ($event->description !== null) {
                $receta->description = $event->description;
            }
            if ($event->instructions !== null) {
                $receta->instructions = $event->instructions;
            }
            if ($event->totalCalories !== null) {
                $receta->totalCalories = $event->totalCalories;
            }

            $this->recetaVersionRepository->save($receta);
        });
    }
}
