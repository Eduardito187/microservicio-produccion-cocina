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
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        ?LoggerInterface $logger = null
    ) {
        $this->calendarioRepository = $calendarioRepository;
        $this->transactionAggregate = $transactionAggregate;
        $this->ventanaEntregaRepository = $ventanaEntregaRepository;
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

            $this->syncCalendarioItems($calendarId, $event->entregaId, $event->contratoId);
        });
    }

    private function syncCalendarioItems(string $calendarioId, ?string $entregaId, ?string $contratoId): void
    {
        if (! is_string($entregaId) || $entregaId === '') {
            return;
        }

        $query = DB::table('item_despacho')
            ->select('id')
            ->where('entrega_id', $entregaId);

        if (is_string($contratoId) && $contratoId !== '') {
            $query->where(function ($q) use ($contratoId): void {
                $q->whereNull('contrato_id')
                    ->orWhere('contrato_id', $contratoId);
            });
        }

        $itemIds = $query->pluck('id');
        $linked = 0;
        foreach ($itemIds as $itemId) {
            if (! is_string($itemId) || $itemId === '') {
                continue;
            }
            $exists = DB::table('calendario_item')
                ->where('calendario_id', $calendarioId)
                ->where('item_despacho_id', $itemId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('calendario_item')->insert([
                'id' => (string) Str::uuid(),
                'calendario_id' => $calendarioId,
                'item_despacho_id' => $itemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $linked++;
        }

        if ($linked > 0) {
            $this->logger->info('Calendario sincronizado con items de despacho', [
                'calendario_id' => $calendarioId,
                'entrega_id' => $entregaId,
                'contrato_id' => $contratoId,
                'items_linked' => $linked,
            ]);
        }
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
