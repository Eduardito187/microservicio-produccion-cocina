<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\ListaDespacho as ListaDespachoModel;
use App\Domain\Produccion\Aggregate\ListaDespacho as AggregateListaDespacho;
use App\Domain\Produccion\Aggregate\ItemDespacho as AggregateItemDespacho;
use App\Domain\Produccion\Repository\ListaDespachoRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Infrastructure\Persistence\Model\ItemDespacho;
use App\Domain\Produccion\Model\DespachoItems;
use DateTimeImmutable;
use DateTimeInterface;

class ListaDespachoRepository implements ListaDespachoRepositoryInterface
{
    /**
     * @var ItemDespachoRepository
     */
    public readonly ItemDespachoRepository $itemDespachoRepository;

    /**
     * Constructor
     * @param ItemDespachoRepository $itemDespachoRepository
     */
    public function __construct(ItemDespachoRepository $itemDespachoRepository) {
        $this->itemDespachoRepository = $itemDespachoRepository;
    }

    /**
     * @param string $id
     * @throws ModelNotFoundException
     * @return AggregateListaDespacho|null
     */
    public function byId(string $id): ?AggregateListaDespacho
    {
        $row = ListaDespachoModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("La lista de despacho id: {$id} no existe.");
        }

        $fecha = $this->mapDateToDomain($row->fecha_entrega);
        $items = $this->mapItemsToDomain($row->items);

        return AggregateListaDespacho::reconstitute(
            $row->id,
            $row->op_id,
            $fecha,
            $row->sucursal_id,
            $items
        );
    }

    /**
     * @param string $orderProduccion
     * @throws ModelNotFoundException
     * @return bool
     */
    public function validToCreate(string $orderProduccion): bool
    {
        $row = ListaDespachoModel::where('op_id', $orderProduccion);

        if ($row) {
            throw new ModelNotFoundException("La orden ya cuenta con una lista de despacho.");
        }

        return true;
    }

    /**
     * @param AggregateListaDespacho $item
     * @param bool $resetItems
     * @return int
     */
    public function save(AggregateListaDespacho $item, bool $resetItems): int
    {
        $model = ListaDespachoModel::query()->updateOrCreate(
            ['id' => $item->id()],
            [
                'op_id' => $item->ordenProduccionId(),
                'fecha_entrega' => $item->fechaEntrega(),
                'sucursal_id' => $item->sucursalId()
            ]
        );
        $dispachoId = $model->id;

        if ($resetItems) {
            ItemDespacho::query()->where('lista_id', $dispachoId)->delete();
            $this->mapItemsToRows($dispachoId, $item->items());
        }

        return $dispachoId;
    }

    /** 
     * @return DespachoItems
     */
    private function mapItemsToDomain($eloquentItems): DespachoItems
    {
        $domainItems = [];

        foreach ($eloquentItems as $item) {
            $domainItems[] = new AggregateItemDespacho(
                $item->id,
                $item->lista_id,
                $item->sku,
                $item->etiqueta_id,
                $item->paciente_id,
                $item->direccion_snapshot,
                $item->ventana_entrega
            );
        }

        return DespachoItems::fromArray($domainItems);
    }

    /**
     * @param int|null $despachoId
     * @param DespachoItems|array $items
     * @return void
     */
    private function mapItemsToRows(int|null $despachoId, DespachoItems|array $items): void
    {
        foreach ($items as $item) {
            $this->itemDespachoRepository->save(
                new AggregateItemDespacho(
                    null,
                    $despachoId,
                    $item->sku()->value,
                    $item->etiquetaId,
                    $item->pacienteId,
                    $item->direccionSnapshot,
                    $item->ventanaEntrega
                )
            );
        }
    }

    /**
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable
     */
    private function mapDateToDomain(string|DateTimeInterface $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable($value . ' 00:00:00');
    }
}