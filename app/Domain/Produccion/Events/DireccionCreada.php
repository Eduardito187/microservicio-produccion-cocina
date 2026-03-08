<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class DireccionCreada
 */
class DireccionCreada extends BaseDomainEvent
{
    /**
     * @var string|null
     */
    private $nombre;

    /**
     * @var string
     */
    private $linea1;

    /**
     * @var string|null
     */
    private $linea2;

    /**
     * @var string|null
     */
    private $ciudad;

    /**
     * @var string|null
     */
    private $provincia;

    /**
     * @var string|null
     */
    private $pais;

    /**
     * @var array|null
     */
    private $geo;

    /**
     * Constructor
     */
    public function __construct(
        string|int|null $direccionId,
        ?string $nombre,
        string $linea1,
        ?string $linea2,
        ?string $ciudad,
        ?string $provincia,
        ?string $pais,
        ?array $geo
    ) {
        parent::__construct($direccionId);
        $this->nombre = $nombre;
        $this->linea1 = $linea1;
        $this->linea2 = $linea2;
        $this->ciudad = $ciudad;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->geo = $geo;
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'linea1' => $this->linea1,
            'linea2' => $this->linea2,
            'ciudad' => $this->ciudad,
            'provincia' => $this->provincia,
            'pais' => $this->pais,
            'geo' => $this->geo,
        ];
    }
}
