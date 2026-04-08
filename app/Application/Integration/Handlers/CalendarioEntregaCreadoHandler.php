<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Handlers;

use App\Application\Integration\Events\CalendarioEntregaCreadoEvent;
use App\Application\Integration\IntegrationEventHandlerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @class SuscripcionCreadaEvent
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
     * @var CalendarioItemRepositoryInterface
     */
    private $calendarioItemRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        CalendarioRepositoryInterface $calendarioRepository,
        TransactionAggregate $transactionAggregate,
        VentanaEntregaRepositoryInterface $ventanaEntregaRepository,
        CalendarioItemRepositoryInterface $calendarioItemRepository,
        ?LoggerInterface $logger = null
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
        $this->calendarioItemRepository = $calendarioItemRepository;
        $this->logger = $logger ?? new NullLogger;
    }

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

            $calendarId = $this->calendarioRepository->save($calendario);

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

            if (is_string($event->entregaId) && $event->entregaId !== '') {
                $linked = $this->calendarioItemRepository->linkItemsByEntregaId(
                    $event->entregaId,
                    $event->contratoId,
                    $calendarId
                );
                if ($linked > 0) {
                    $this->logger->info('Calendario sincronizado con items de despacho', [
                        'calendario_id' => $calendarId,
                        'entrega_id' => $event->entregaId,
                        'contrato_id' => $event->contratoId,
                        'items_linked' => $linked,
                    ]);
                }
            }
        });
    }

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
