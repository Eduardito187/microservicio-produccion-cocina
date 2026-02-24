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
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $instructions;

    /**
     * @var int|null
     */
    private $totalCalories;

    /**
     * Constructor
     *
     * @param string|int|null $recetaId
     * @param string $nombre
     * @param int $version
     * @param array|null $nutrientes
     * @param array|null $ingredientes
     * @param string|null $description
     * @param string|null $instructions
     * @param int|null $totalCalories
     */
    public function __construct(
        string|int|null $recetaId,
        string $nombre,
        int $version,
        array|null $nutrientes,
        array|null $ingredientes,
        string|null $description = null,
        string|null $instructions = null,
        int|null $totalCalories = null
    ) {
        parent::__construct($recetaId);
        $this->nombre = $nombre;
        $this->version = $version;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->description = $description;
        $this->instructions = $instructions;
        $this->totalCalories = $totalCalories;
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
            'description' => $this->description,
            'instructions' => $this->instructions,
            'totalCalories' => $this->totalCalories,
        ];
    }
}
