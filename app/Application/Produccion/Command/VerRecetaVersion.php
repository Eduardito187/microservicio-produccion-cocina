<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Command;

/**
 * @class VerRecetaVersion
 * @package App\Application\Produccion\Command
 */
class VerRecetaVersion
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
