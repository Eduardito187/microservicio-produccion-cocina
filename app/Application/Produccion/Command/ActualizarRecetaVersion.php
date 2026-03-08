<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class ActualizarRecetaVersion
 */
class ActualizarRecetaVersion
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $nombre;

    /**
     * @var array|null
     */
    public $nutrientes;

    /**
     * @var array|null
     */
    public $ingredientes;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $instructions;

    /**
     * @var int|null
     */
    public $totalCalories;

    /**
     * Constructor
     */
    public function __construct(
        string $id,
        string $nombre,
        ?array $nutrientes = null,
        ?array $ingredientes = null,
        ?string $description = null,
        ?string $instructions = null,
        ?int $totalCalories = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->description = $description;
        $this->instructions = $instructions;
        $this->totalCalories = $totalCalories;
    }
}
