<?php

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Shared\ValueObjects\ValueObject;

class ItemDespacho extends ValueObject
{
    /**
     * @var int|null
     */
    public readonly int|null $listaId;

    /**
     * @var Sku
     */
    public Sku $sku;

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
     * @param int|null $listaId
     * @param Sku $sku
     * @param int $etiquetaId
     * @param int $pacienteId
     * @param array $direccionSnapshot
     * @param array $ventanaEntrega
     */
    public function __construct(
        int|null $listaId,
        Sku $sku,
        int $etiquetaId,
        int $pacienteId,
        array $direccionSnapshot = [],
        array $ventanaEntrega = []
    ) {
        $this->listaId = $listaId;
        $this->sku = $sku;
        $this->etiquetaId = $etiquetaId;
        $this->pacienteId = $pacienteId;
        $this->direccionSnapshot = $direccionSnapshot;
        $this->ventanaEntrega = $ventanaEntrega;
    }

    /**
     * @return Sku
     */
    public function sku(): Sku
    {
        return $this->sku;
    }
}