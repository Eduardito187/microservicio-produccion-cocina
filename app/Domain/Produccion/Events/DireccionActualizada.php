<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class DireccionActualizada
 * @package App\Domain\Produccion\Events
 */
class DireccionActualizada extends BaseDomainEvent
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
     *
     * @param string|int|null $direccionId
     * @param string|null $nombre
     * @param string $linea1
     * @param string|null $linea2
     * @param string|null $ciudad
     * @param string|null $provincia
     * @param string|null $pais
     * @param array|null $geo
     */
    public function __construct(
        string|int|null $direccionId,
        string|null $nombre,
        string $linea1,
        string|null $linea2,
        string|null $ciudad,
        string|null $provincia,
        string|null $pais,
        array|null $geo
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

    /**
     * @return array
     */
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
