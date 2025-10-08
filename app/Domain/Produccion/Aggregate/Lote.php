<?php

namespace App\Domain\Produccion\Aggregate;

use InvalidArgumentException;

class Lote
{
    /**
     * @var string
     */
    public readonly string $id;

    /**
     * @var string
     */
    public readonly string $opId;

    /**
     * @var string
     */
    public readonly string $estacionId;

    /**
     * @var string
     */
    public readonly string $recetaVersionId;

    /**
     * @var string
     */
    public readonly string $porcionId;

    /**
     * @var int
     */
    public $cantidadPlanificada;

    /**
     * @var int
     */
    public $cantidadProducida;

    /**
     * @var int
     */
    public $mermaGr;

    /**
     * @var string
     */
    public $estado;

    /**
     * Constructor
     * 
     * @param string $id
     * @param string $opId
     * @param string $estacionId
     * @param string $recetaVersionId
     * @param string $porcionId
     * @param int $cantidadPlanificada
     * @param int $cantidadProducida
     * @param int $mermaGr
     * @param string $estado
     */
    public function __construct(
        string $id,
        string $opId,
        string $estacionId,
        string $recetaVersionId,
        string $porcionId,
        int $cantidadPlanificada,
        int $cantidadProducida = 0,
        int $mermaGr = 0,
        string $estado = 'PLANIFICADO',
    ) {
        $this->id = $id;
        $this->opId = $opId;
        $this->estacionId = $estacionId;
        $this->recetaVersionId = $recetaVersionId;
        $this->porcionId = $porcionId;
        $this->cantidadPlanificada = $cantidadPlanificada;
        $this->cantidadProducida = $cantidadProducida;
        $this->mermaGr = $mermaGr;
        $this->estado = $estado;
    }

    /**
     * @param int $producida
     * @param int $mermaGr
     * @throws InvalidArgumentException
     * @return void
     */
    public function cerrar(int $producida, int $mermaGr): void
    {
        if ($producida < 0 || $mermaGr < 0) throw new InvalidArgumentException();

        $this->cantidadProducida = $producida;
        $this->mermaGr = $mermaGr;
        $this->estado = 'CERRADO';
    }
}