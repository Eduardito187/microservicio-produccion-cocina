<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\CalendarioEntregaCreadoEvent;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Entity\VentanaEntrega;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class SuscripcionCreadaEvent
 * @package App\Application\Integration\Handlers
 */
class CalendarioEntregaCreadoHandler implements IntegrationEventHandlerInterface
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
     * @var VentanaEntregaRepositoryInterface
     */
    private $ventanaEntregaRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param CalendarioRepositoryInterface $calendarioRepository
     * @param TransactionAggregate $transactionAggregate
     * @param VentanaEntregaRepositoryInterface $ventanaEntregaRepository
     * @param ?LoggerInterface $logger
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate,
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        ?LoggerInterface $logger = null
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array $payload
     * @param array $meta
     * @return void
     */
    public function handle(array $payload, array $meta = []): void
    {
        $event = CalendarioEntregaCreadoEvent::fromPayload($payload);

        $this->transactionAggregate->runTransaction(function () use ($event): void {
            $calendario = new Calendario(
                $event->id,
                new DateTimeImmutable($event->fecha),
                $event->entregaId,
                $event->contratoId,
                $event->estado
            );

            $this->calendarioRepository->save($calendario);

            if (is_string($event->hora) && $event->hora !== '') {
                try {
                    $desde = new DateTimeImmutable($event->fecha . ' ' . $event->hora);
                    $hasta = $desde->modify('+30 minutes');
                    $ventanaId = $this->buildVentanaId(
                        $event->entregaId ?? $event->id,
                        $event->fecha,
                        $event->hora
                    );
                    $this->ventanaEntregaRepository->save(new VentanaEntrega(
                        $ventanaId,
                        $desde,
                        $hasta,
                        $event->entregaId,
                        $event->contratoId,
                        $event->estado
                    ));
                } catch (\Throwable $e) {
                    $this->logger->warning('Ventana de entrega no creada (fecha/hora invalida)', [
                        'calendario_id' => $event->id,
                        'fecha' => $event->fecha,
                        'hora' => $event->hora,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * @param string $baseId
     * @param string $fecha
     * @param string $hora
     * @return string
     */
    private function buildVentanaId(string $baseId, string $fecha, string $hora): string
    {
        $hash = md5($baseId . '|' . $fecha . '|' . $hora);
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
