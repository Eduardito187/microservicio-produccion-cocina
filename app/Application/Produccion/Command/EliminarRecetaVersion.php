<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class EliminarRecetaVersion
 * @package App\Application\Produccion\Command
 */
class EliminarRecetaVersion
{
    /**
     * @var string
     */
    public $id;

    /**
     * Constructor
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
