<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class RecetaVersionActualizada
 * @package App\Domain\Produccion\Events
 */
class RecetaVersionActualizada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $nombre;

    /**
     * @var int
     */
    private $version;

    /**
     * @var array|null
     */
    private $nutrientes;

    /**
     * @var array|null
     */
    private $ingredientes;

    /**
     * Constructor
     *
     * @param string|int|null $recetaVersionId
     * @param string $nombre
     * @param int $version
     * @param array|null $nutrientes
     * @param array|null $ingredientes
     */
    public function __construct(
        string|int|null $recetaVersionId,
        string $nombre,
        int $version,
        array|null $nutrientes,
        array|null $ingredientes
    ) {
        parent::__construct($recetaVersionId);
        $this->nombre = $nombre;
        $this->version = $version;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'version' => $this->version,
            'nutrientes' => $this->nutrientes,
            'ingredientes' => $this->ingredientes,
        ];
    }
}
