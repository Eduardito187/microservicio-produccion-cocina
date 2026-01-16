<?php

namespace App\Application\Produccion\Command;

class EliminarCalendario
{
    /**
     * @var int
     */
    public int $id;

    /**
     * Constructor
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
