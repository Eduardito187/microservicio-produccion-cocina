<?php

namespace App\Application\Produccion\Command;

class ProcesadorOP
{
    /**
     * @var int
     */
    public readonly string $opId;

    /**
     * Constructor
     * 
     * @param string $opId
     */
    public function __construct(
        string $opId
    ) {
        $this->opId = $opId;
    }
}



