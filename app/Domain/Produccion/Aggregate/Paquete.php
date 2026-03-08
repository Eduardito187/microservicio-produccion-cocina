<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Shared\Aggregate\AggregateRoot;

/**
 * @class Paquete
 */
class Paquete
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    private $id;

    /**
     * @var string|int
     */
    private $etiquetaId;

    /**
     * @var string|int
     */
    private $ventanaId;

    /**
     * @var string|int
     */
    private $direccionId;

    /**
     * Constructor
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

        // $self->record();

        return $self;
    }

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
