<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\OrdenProduccion as OrdenProduccionModel;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Infrastructure\Persistence\Model\VentanaEntrega as VentanaEntregaModel;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use App\Infrastructure\Persistence\Model\Direccion as DireccionModel;
use App\Infrastructure\Persistence\Model\Paciente as PacienteModel;
use App\Infrastructure\Persistence\Model\Etiqueta as EtiquetaModel;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Infrastructure\Persistence\Model\Paquete as PaqueteModel;
use App\Domain\Produccion\Events\PaqueteParaDespachoCreado;
use App\Application\Shared\DomainEventPublisherInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoOP;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @class OrdenProduccionRepository
 * @package App\Infrastructure\Persistence\Repository
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
     *
     * @param OrdenItemRepository $ordenItemRepository
     * @param ItemDespachoRepository $itemDespachoRepository
     * @param ProduccionBatchRepository $produccionBatchRepository
     * @param DomainEventPublisherInterface $eventPublisher
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
     * @param string|null $id
     * @throws ModelNotFoundException
     * @return AggregateOrdenProduccion|null
     */
    public function byId(string|null $id): ?AggregateOrdenProduccion
    {
        $row = OrdenProduccionModel::query()
            ->with(['items.product', 'batches', 'despachoItems'])
            ->find($id);

        if (!$row) {
            throw new ModelNotFoundException("La orden de produccion id: {$id} no existe.");
        }

        $fecha = $this->convertDate($row->fecha);
        $estado = EstadoOP::from($row->estado);
        $items = $this->mapItems($row->items);
        $batches = $this->mapItemsBatches($row->batches);
        $itemsDespacho = $this->mapItemsDespachos($row->despachoItems);

        return AggregateOrdenProduccion::reconstitute(
            $row->id,
            $fecha,
            $row->sucursal_id,
            $estado,
            $items,
            $batches,
            $itemsDespacho
        );
    }

    /**
     * @param AggregateOrdenProduccion $aggregateOrdenProduccion
     * @return int
     */
    public function save(AggregateOrdenProduccion $aggregateOrdenProduccion): string
    {
        $model = OrdenProduccionModel::query()->updateOrCreate(
            ['id' => $aggregateOrdenProduccion->id()],
            [
                'fecha' => $aggregateOrdenProduccion->fecha()->format('Y-m-d'),
                'sucursal_id' => $aggregateOrdenProduccion->sucursalId(),
                'estado' => $aggregateOrdenProduccion->estado()->value
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
     * @param mixed $data
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
     * @param mixed $data
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
                $row->estacion_id,
                $row->receta_version_id,
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
     * @param mixed $data
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
                null,
                null,
                null,
                null
            );
        }

        return $items;
    }

    /**
     * @param string|null $opId
     * @param OrdenItem[] $items
     * @return void
     */
    private function savedItems(string|null $opId, array $items): void
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
     * @param string|null $opId
     * @param array $items
     * @return void
     */
    private function savedBatch(array $items): void
    {
        foreach ($items as $key => $item) {
            $batchId = $this->produccionBatchRepository->save(
                new AggregateProduccionBatch(
                    $item->id,
                    $item->ordenProduccionId,
                    $item->productoId,
                    $item->estacionId,
                    $item->recetaVersionId,
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
                        $item->estacionId,
                        $item->productoId,
                        $item->recetaVersionId,
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

    /**
     * @param array $items
     * @return void
     */
    private function savedDespacho(array $items): void
    {
        foreach ($items as $item) {
            $paqueteId = $item->paqueteId ?? $this->resolvePaqueteId($item);

            $this->itemDespachoRepository->save(
                new ItemDespacho(
                    $item->id,
                    $item->ordenProduccionId,
                    $item->productId,
                    $paqueteId,
                    $item->recetaVersionId,
                    $item->pacienteId,
                    $item->direccionId,
                    $item->ventanaEntregaId
                )
            );
        }
    }

    /**
     * @param ItemDespacho $item
     * @return string|null
     */
    private function resolvePaqueteId(ItemDespacho $item): string|null
    {
        if (
            $item->recetaVersionId === null
            || $item->pacienteId === null
            || $item->direccionId === null
            || $item->ventanaEntregaId === null
        ) {
            return null;
        }

        $paciente = PacienteModel::find($item->pacienteId);
        if (!$paciente) {
            return null;
        }

        $direccion = DireccionModel::find($item->direccionId);
        if (!$direccion) {
            return null;
        }

        $ventana = VentanaEntregaModel::find($item->ventanaEntregaId);
        if (!$ventana) {
            return null;
        }

        $etiqueta = EtiquetaModel::firstOrCreate(
            [
                'receta_version_id' => $item->recetaVersionId,
                'paciente_id' => $paciente->id,
            ],
            [
                'suscripcion_id' => $paciente->suscripcion_id,
                'qr_payload' => [],
            ]
        );

        $paquete = PaqueteModel::firstOrCreate(
            [
                'etiqueta_id' => $etiqueta->id,
                'ventana_id' => $ventana->id,
                'direccion_id' => $direccion->id,
            ]
        );

        if ($paquete->wasRecentlyCreated) {
            $event = new PaqueteParaDespachoCreado(
                $paquete->id,
                $etiqueta->id,
                $ventana->id,
                $direccion->id,
                $paciente->id,
                $etiqueta->receta_version_id,
                $etiqueta->suscripcion_id
            );
            $this->eventPublisher->publish([$event], $paquete->id);
        }

        return $paquete->id;
    }

    /**
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable
     */
    private function convertDate(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}
