<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoOP;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Events\PaqueteParaDespachoCreado;
use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Model\Direccion as DireccionModel;
use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;
use App\Infrastructure\Persistence\Model\OrdenProduccion as OrdenProduccionModel;
use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;
use App\Infrastructure\Persistence\Model\Paquete as PaqueteModel;
use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class OrdenProduccionRepository
 */
class OrdenProduccionRepository implements OrdenProduccionRepositoryInterface
{
    /**
     * @var OrdenItemRepository
     */
    private $ordenItemRepository;

    /**
     * @var ItemDespachoRepository
     */
    private $itemDespachoRepository;

    /**
     * @var ProduccionBatchRepository
     */
    private $produccionBatchRepository;

    /**
     * @var DomainEventPublisherInterface
     */
    private $eventPublisher;

    /**
     * Constructor
     */
    public function __construct(
        OrdenItemRepository $ordenItemRepository,
        ItemDespachoRepository $itemDespachoRepository,
        ProduccionBatchRepository $produccionBatchRepository,
        DomainEventPublisherInterface $eventPublisher
    ) {
        $this->ordenItemRepository = $ordenItemRepository;
        $this->itemDespachoRepository = $itemDespachoRepository;
        $this->produccionBatchRepository = $produccionBatchRepository;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function byId(?string $id): ?AggregateOrdenProduccion
    {
        $row = OrdenProduccionModel::query()
            ->with(['items.product', 'batches', 'despachoItems'])
            ->find($id);

        if (! $row) {
            throw new EntityNotFoundException("La orden de produccion id: {$id} no existe.");
        }

        $fecha = $this->convertDate($row->fecha);
        $estado = EstadoOP::from($row->estado);
        $items = $this->mapItems($row->items);
        $batches = $this->mapItemsBatches($row->batches);
        $itemsDespacho = $this->mapItemsDespachos($row->despachoItems);

        return AggregateOrdenProduccion::reconstitute(
            $row->id,
            $fecha,
            $estado,
            $items,
            $batches,
            $itemsDespacho
        );
    }

    /**
     * @return int
     */
    public function save(AggregateOrdenProduccion $aggregateOrdenProduccion): string
    {
        $model = OrdenProduccionModel::query()->updateOrCreate(
            ['id' => $aggregateOrdenProduccion->id()],
            [
                'fecha' => $aggregateOrdenProduccion->fecha()->format('Y-m-d'),
                'estado' => $aggregateOrdenProduccion->estado()->value,
            ]
        );
        $orderId = $model->id;

        $this->savedItems($orderId, $aggregateOrdenProduccion->items());
        $this->savedBatch($aggregateOrdenProduccion->batches());
        $this->savedDespacho($aggregateOrdenProduccion->itemsDespacho());
        $this->eventPublisher->publish($aggregateOrdenProduccion->pullEvents(), $orderId);

        return $orderId;
    }

    /**
     * @param  mixed  $data
     * @return OrdenItem[]
     */
    private function mapItems($data): array
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new OrdenItem(
                $row->id,
                $row->op_id,
                $row->p_id,
                new Qty($row->qty),
                new Sku(value: $row->product->sku),
                $row->price,
                $row->final_price
            );
        }

        return $items;
    }

    /**
     * @param  mixed  $data
     * @return AggregateProduccionBatch[]
     */
    private function mapItemsBatches($data): array
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new AggregateProduccionBatch(
                $row->id,
                $row->op_id,
                $row->p_id,
                $row->porcion_id,
                $row->cant_planificada,
                $row->cant_producida,
                $row->merma_gr,
                EstadoPlanificado::from($row->estado),
                $row->rendimiento,
                new Qty($row->qty),
                $row->posicion,
                $row->ruta
            );
        }

        return $items;
    }

    /**
     * @param  mixed  $data
     * @return ItemDespacho[]
     */
    private function mapItemsDespachos($data): array
    {
        $items = [];

        foreach ($data as $row) {
            $items[] = new ItemDespacho(
                $row->id,
                $row->op_id,
                $row->product_id,
                $row->paquete_id,
                $row->paciente_id ?? null,
                $row->direccion_id ?? null,
                $row->ventana_entrega_id ?? null,
                $row->entrega_id ?? null,
                $row->contrato_id ?? null,
                $row->driver_id ?? null
            );
        }

        return $items;
    }

    /**
     * @param  OrdenItem[]  $items
     */
    private function savedItems(?string $opId, array $items): void
    {
        foreach ($items as $item) {
            $this->ordenItemRepository->save(
                new OrdenItem(
                    $item->id,
                    $opId,
                    null,
                    $item->qty,
                    $item->sku
                )
            );
        }
    }

    /**
     * @param  string|null  $opId
     */
    private function savedBatch(array $items): void
    {
        foreach ($items as $key => $item) {
            $batchId = $this->produccionBatchRepository->save(
                new AggregateProduccionBatch(
                    $item->id,
                    $item->ordenProduccionId,
                    $item->productoId,
                    $item->porcionId,
                    $item->cantPlanificada,
                    $item->cantProducida,
                    $item->mermaGr,
                    $item->estado,
                    $item->rendimiento,
                    $item->qty,
                    $item->posicion
                )
            );

            $events = [];
            foreach ($item->pullEvents() as $event) {
                if ($event instanceof ProduccionBatchCreado && $event->aggregateId() === null) {
                    $events[] = new ProduccionBatchCreado(
                        $batchId,
                        $item->ordenProduccionId,
                        $item->productoId,
                        $item->porcionId,
                        $item->qty,
                        $item->posicion
                    );
                } else {
                    $events[] = $event;
                }
            }

            $this->eventPublisher->publish($events, $item->ordenProduccionId);
        }
    }

    private function savedDespacho(array $items): void
    {
        foreach ($items as $item) {
            $paqueteId = $item->paqueteId ?? $this->resolvePaqueteId($item);
            $entregaId = $item->entregaId ?? $this->resolveEntregaIdFromVentana($item->ventanaEntregaId);
            $contratoId = $item->contratoId ?? $this->resolveContratoIdFromVentana($item->ventanaEntregaId);

            $this->itemDespachoRepository->save(
                new ItemDespacho(
                    $item->id,
                    $item->ordenProduccionId,
                    $item->productId,
                    $paqueteId,
                    $item->pacienteId,
                    $item->direccionId,
                    $item->ventanaEntregaId,
                    $entregaId,
                    $contratoId,
                    $item->driverId
                )
            );
        }
    }

    private function resolveEntregaIdFromVentana(string|int|null $ventanaEntregaId): ?string
    {
        if (! is_string($ventanaEntregaId) || $ventanaEntregaId === '') {
            return null;
        }

        $ventana = VentanaEntregaModel::find($ventanaEntregaId);
        if ($ventana === null) {
            return null;
        }

        $entregaId = $ventana->entrega_id ?? null;

        return is_string($entregaId) && $entregaId !== '' ? $entregaId : null;
    }

    private function resolveContratoIdFromVentana(string|int|null $ventanaEntregaId): ?string
    {
        if (! is_string($ventanaEntregaId) || $ventanaEntregaId === '') {
            return null;
        }

        $ventana = VentanaEntregaModel::find($ventanaEntregaId);
        if ($ventana === null) {
            return null;
        }

        $contratoId = $ventana->contrato_id ?? null;

        return is_string($contratoId) && $contratoId !== '' ? $contratoId : null;
    }

    private function resolvePaqueteId(ItemDespacho $item): ?string
    {
        if (
            $item->pacienteId === null
            || $item->direccionId === null
            || $item->ventanaEntregaId === null
        ) {
            return null;
        }

        $paciente = PacienteModel::find($item->pacienteId);
        if (! $paciente) {
            return null;
        }

        $direccion = DireccionModel::find($item->direccionId);
        if (! $direccion) {
            return null;
        }

        $ventana = VentanaEntregaModel::find($item->ventanaEntregaId);
        if (! $ventana) {
            return null;
        }

        $etiqueta = EtiquetaModel::firstOrCreate(
            ['paciente_id' => $paciente->id],
            ['suscripcion_id' => $paciente->suscripcion_id, 'qr_payload' => []]
        );

        if ($etiqueta->suscripcion_id === null && $paciente->suscripcion_id !== null) {
            $etiqueta->update(['suscripcion_id' => $paciente->suscripcion_id]);
        }

        $paquete = PaqueteModel::firstOrCreate(
            [
                'etiqueta_id' => $etiqueta->id,
                'ventana_id' => $ventana->id,
                'direccion_id' => $direccion->id,
            ]
        );

        $geo = is_array($direccion->geo) ? $direccion->geo : [];
        $event = new PaqueteParaDespachoCreado(
            $paquete->id,
            $this->buildPackageNumber($paquete->id),
            $paciente->id,
            (string) $paciente->nombre,
            $this->buildDeliveryAddress($direccion),
            $this->extractLatitude($geo),
            $this->extractLongitude($geo),
            $ventana->desde->format('Y-m-d')
        );
        $this->eventPublisher->publish([$event], $paquete->id);

        return $paquete->id;
    }

    public function markEntregaCompletada(string $opId, DateTimeImmutable $completedAt): void
    {
        OrdenProduccionModel::query()
            ->where('id', $opId)
            ->whereNull('entrega_completada_at')
            ->update([
                'entrega_completada_at' => $completedAt->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);
    }

    private function convertDate(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }

    private function buildPackageNumber(string $packageId): string
    {
        $normalized = strtoupper(str_replace('-', '', $packageId));

        return 'PKG-' . substr($normalized, 0, 12);
    }

    private function buildDeliveryAddress(DireccionModel $direccion): string
    {
        $parts = array_filter([
            $direccion->linea1,
            $direccion->linea2,
            $direccion->ciudad,
            $direccion->provincia,
            $direccion->pais,
        ], static fn ($value) => is_string($value) && trim($value) !== '');

        return implode(', ', $parts);
    }

    private function extractLatitude(array $geo): float
    {
        $value = $geo['lat'] ?? $geo['latitude'] ?? 0;

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function extractLongitude(array $geo): float
    {
        $value = $geo['lng'] ?? $geo['lon'] ?? $geo['longitude'] ?? 0;

        return is_numeric($value) ? (float) $value : 0.0;
    }
}
