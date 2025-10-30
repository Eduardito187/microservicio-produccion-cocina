<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

class Paquete
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    private int|null $id;

    /**
     * @var int
     */
    private int $etiquetaId;

    /**
     * @var int
     */
    private int $ventanaId;

    /**
     * @var int
     */
    private int $direccionId;

    /**
     * Constructor
     * @param int|null $id
     * @param int $etiquetaId
     * @param int $ventanaId
     * @param int $direccionId
     */
    public function __construct(
        int|null $id,
        int $etiquetaId,
        int $ventanaId,
        int $direccionId
    ) {
        $this->id = $id;
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }

    /**
     * @param int|null $id
     * @param int $etiquetaId
     * @param int $ventanaId
     * @param int $direccionId
     * @return Paquete
     */
    public static function crear(
        int|null $id,
        int $etiquetaId,
        int $ventanaId,
        int $direccionId
    ): self {
        $self = new self(
            $id,
            $etiquetaId,
            $ventanaId,
            $direccionId
        );

        //$self->record();

        return $self;
    }

    /**
     * @param int $id
     * @param int $etiquetaId
     * @param int $ventanaId
     * @param int $direccionId
     * @return Paquete
     */
    public static function reconstitute(
        int $id,
        int $etiquetaId,
        int $ventanaId,
        int $direccionId
    ): self {
        $self = new self(
            $id,
            $etiquetaId,
            $ventanaId,
            $direccionId
        );

        return $self;
    }
}