<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Events;

use App\Domain\Shared\Events\BaseDomainEvent;

/**
 * @class RecetaVersionActualizada
 */
class RecetaVersionActualizada extends BaseDomainEvent
{
    /**
     * @var string
     */
    private $nombre;

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
     */
    public function __construct(
        string|int|null $recetaId,
        string $nombre,
        ?array $nutrientes,
        ?array $ingredientes,
        ?string $description = null,
        ?string $instructions = null,
        ?int $totalCalories = null
    ) {
        parent::__construct($recetaId);
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->description = $description;
        $this->instructions = $instructions;
        $this->totalCalories = $totalCalories;
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'nutrientes' => $this->nutrientes,
            'ingredientes' => $this->ingredientes,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'totalCalories' => $this->totalCalories,
        ];
    }
}
