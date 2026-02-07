<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

class Paquete
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    private string|int|null $id;

    /**
     * @var int
     */
    private string|int $etiquetaId;

    /**
     * @var int
     */
    private string|int $ventanaId;

    /**
     * @var int
     */
    private string|int $direccionId;

    /**
     * Constructor
     * @param string|int|null $id
     * @param string|int $etiquetaId
     * @param string|int $ventanaId
     * @param string|int $direccionId
     */
    public function __construct(
        string|int|null $id,
        string|int $etiquetaId,
        string|int $ventanaId,
        string|int $direccionId
    ) {
        $this->id = $id;
        $this->etiquetaId = $etiquetaId;
        $this->ventanaId = $ventanaId;
        $this->direccionId = $direccionId;
    }

    /**
     * @param string|int|null $id
     * @param string|int $etiquetaId
     * @param string|int $ventanaId
     * @param string|int $direccionId
     * @return Paquete
     */
    public static function crear(
        string|int|null $id,
        string|int $etiquetaId,
        string|int $ventanaId,
        string|int $direccionId
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
     * @param string|int $etiquetaId
     * @param string|int $ventanaId
     * @param string|int $direccionId
     * @return Paquete
     */
    public static function reconstitute(
        int $id,
        string|int $etiquetaId,
        string|int $ventanaId,
        string|int $direccionId
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