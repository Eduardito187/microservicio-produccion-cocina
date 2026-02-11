<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class VerDireccion
 * @package App\Application\Produccion\Command
 */
class VerDireccion
{
    /**
     * @var int
     */
    public string $id;

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
