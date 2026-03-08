<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class CalendarioGeneradoHandler
 */
class CalendarioGeneradoHandler implements IntegrationEventHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate,
        ?LoggerInterface $logger = null
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->logger = $logger ?? new NullLogger;
    }

    public function handle(array $payload, array $meta = []): void
    {
        $contratoId = isset($payload['contratoId']) && is_string($payload['contratoId'])
            ? $payload['contratoId']
            : 'contrato';
        $fechas = $payload['listaFechasEntrega'] ?? [];
        if (! is_array($fechas)) {
            return;
        }

        foreach ($fechas as $fechaRaw) {
            if (! is_string($fechaRaw) || trim($fechaRaw) === '') {
                continue;
            }

            try {
                $fecha = (new DateTimeImmutable($fechaRaw))->format('Y-m-d');
                $id = $this->buildId($contratoId, $fecha);
                $this->transactionAggregate->runTransaction(function () use ($id, $fecha): void {
                    $this->calendarioRepository->save(
                        new Calendario($id, new DateTimeImmutable($fecha))
                    );
                });
            } catch (\Throwable $e) {
                $this->logger->warning('Fecha de calendario invalida ignorada', [
                    'fecha' => $fechaRaw,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildId(string $contratoId, string $fecha): string
    {
        $hash = md5($contratoId . '|' . $fecha);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }
}
