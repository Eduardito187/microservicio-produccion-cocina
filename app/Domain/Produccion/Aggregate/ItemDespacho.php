<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ItemDespachoCreado;
use App\Domain\Shared\Aggregate\AggregateRoot;

class ItemDespacho
{
    use AggregateRoot;

    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $productId;

    /**
     * @var int|null
     */
    public readonly int|null $paqueteId;

    /**
     * Constructor
     * 
     * @param int $ordenProduccionId
     * @param int $productId
     * @param int|null $paqueteId
     */
    public function __construct(
        int $ordenProduccionId,
        int $productId,
        int|null $paqueteId
    ) {
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productId = $productId;
        $this->paqueteId = $paqueteId;
    }

    /**
     * @param int $ordenProduccionId
     * @param int $productId
     * @param int|null $paqueteId
     * @return ItemDespacho
     */
    public static function crear(
        int $ordenProduccionId,
        int $productId,
        int|null $paqueteId
    ): self {
        $self = new self(
            $ordenProduccionId,
            $productId,
            $paqueteId
        );

        $self->record(new ItemDespachoCreado( $ordenProduccionId, $productId, $paqueteId));

        return $self;
    }
}