<?php

namespace App\Application\Produccion\Command;

class ProcesadorOP
{
    /**
     * @var int
     */
    public readonly int $opId;

    /**
     * Constructor
     * 
     * @param int $opId
     */
    public function __construct(
        int $opId
    ) {
        $this->opId = $opId;
    }
}