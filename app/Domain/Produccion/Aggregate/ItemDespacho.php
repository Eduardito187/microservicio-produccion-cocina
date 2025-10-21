<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ItemDespachoCreado;
use App\Domain\Shared\Aggregate\AggregateRoot;

class ItemDespacho
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    public readonly int|null $id;

    /**
     * @var int
     */
    public readonly int $listaId;

    /**
     * @var string
     */
    public readonly string $sku;

    /**
     * @var int
     */
    public readonly int $etiquetaId;

    /**
     * @var int
     */
    public readonly int $pacienteId;

    /**
     * @var array
     */
    public readonly array $direccionSnapshot;

    /**
     * @var array
     */
    public readonly array $ventanaEntrega;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $listaId
     * @param string $sku
     * @param int $etiquetaId
     * @param int $pacienteId
     * @param array $direccionSnapshot
     * @param array $ventanaEntrega
     */
    public function __construct(
        int|null $id,
        int $listaId,
        string $sku,
        int $etiquetaId,
        int $pacienteId,
        array $direccionSnapshot,
        array $ventanaEntrega
    ) {
        $this->id = $id;
        $this->listaId = $listaId;
        $this->sku = $sku;
        $this->etiquetaId = $etiquetaId;
        $this->pacienteId = $pacienteId;
        $this->direccionSnapshot = $direccionSnapshot;
        $this->ventanaEntrega = $ventanaEntrega;
    }

    /**
     * @param int|null $id
     * @param int $listaId
     * @param string $sku
     * @param int $etiquetaId
     * @param int $pacienteId
     * @param array $direccionSnapshot
     * @param array $ventanaEntrega
     * @return ItemDespacho
     */
    public static function crear(
        int|null $id,
        int $listaId,
        string $sku,
        int $etiquetaId,
        int $pacienteId,
        array $direccionSnapshot,
        array $ventanaEntrega
    ): self {
        $self = new self(
            $id,
            $listaId,
            $sku,
            $etiquetaId,
            $pacienteId,
            $direccionSnapshot,
            $ventanaEntrega
        );

        $self->record(new ItemDespachoCreado( $listaId, $etiquetaId));

        return $self;
    }
}