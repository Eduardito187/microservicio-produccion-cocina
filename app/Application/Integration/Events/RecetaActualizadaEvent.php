<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

/**
 * @class RecetaActualizadaEvent
 * @package App\Application\Integration\Events
 */
class RecetaActualizadaEvent
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var ?string
     */
    public $nombre;

    /**
     * @var ?array
     */
    public $nutrientes;

    /**
     * @var ?array
     */
    public $ingredientes;

    /**
     * @var ?int
     */
    public $version;

    /**
     * Constructor
     *
     * @param string $id
     * @param ?string $nombre
     * @param ?array $nutrientes
     * @param ?array $ingredientes
     * @param ?int $version
     */
    public function __construct(
        string $id,
        ?string $nombre,
        ?array $nutrientes,
        ?array $ingredientes,
        ?int $version
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->version = $version;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'recetaVersionId', 'receta_version_id', 'recetaId', 'receta_id'], null, true),
            $p->getString(['nombre', 'name']),
            $p->getArray(['nutrientes', 'nutrients']),
            $p->getArray(['ingredientes', 'ingredients']),
            $p->getInt(['version', 'versionNumber'])
        );
    }
}
