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
     * @var ?string
     */
    public $description;

    /**
     * @var ?string
     */
    public $instructions;

    /**
     * @var ?int
     */
    public $totalCalories;

    /**
     * Constructor
     *
     * @param string $id
     * @param ?string $nombre
     * @param ?array $nutrientes
     * @param ?array $ingredientes
     * @param ?string $description
     * @param ?string $instructions
     * @param ?int $totalCalories
     */
    public function __construct(
        string $id,
        ?string $nombre,
        ?array $nutrientes,
        ?array $ingredientes,
        ?string $description,
        ?string $instructions,
        ?int $totalCalories
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->nutrientes = $nutrientes;
        $this->ingredientes = $ingredientes;
        $this->description = $description;
        $this->instructions = $instructions;
        $this->totalCalories = $totalCalories;
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        $nutrientes = $p->getArray(['nutrientes', 'nutrients']);
        $totalCalories = $p->getInt(['totalCalories', 'total_calories']);
        if ($nutrientes === null && $totalCalories !== null) {
            $nutrientes = ['kcal' => $totalCalories];
        }

        return new self(
            $p->getString(['id', 'recetaId', 'receta_id'], null, true),
            $p->getString(['nombre', 'name']),
            $nutrientes,
            $p->getArray(['ingredientes', 'ingredients']),
            $p->getString(['description', 'descripcion']),
            $p->getString(['instructions', 'instrucciones']),
            $totalCalories
        );
    }
}
