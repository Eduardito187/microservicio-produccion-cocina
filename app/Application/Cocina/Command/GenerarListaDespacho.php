<?php

namespace App\Application\Cocina\Command;

class GenerarListaDespacho
{
    /**
     * @var string
     */
    public readonly string $ordenProduccionId;

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
     * @var string
     */
    public readonly string $cantPlanificada;

    /**
     * @var string
     */
    public readonly string $cantProducida;

    /**
     * @var string
     */
    public readonly string $mermaGr;

    /**
     * @var string
     */
    public readonly string $estado;

    /**
     * Constructor
     * 
     * @param string $ordenProduccionId
     * @param string $estacionId
     * @param string $recetaVersionId
     * @param string $porcionId
     * @param string $cantPlanificada
     * @param string $cantProducida
     * @param string $mermaGr
     * @param string $estado
     */
    public function __construct(
        string $ordenProduccionId,
        string $estacionId,
        string $recetaVersionId,
        string $porcionId,
        string $cantPlanificada,
        string $cantProducida,
        string $mermaGr,
        string $estado
    ) {
        $this->ordenProduccionId = $ordenProduccionId;
        $this->estacionId = $estacionId;
        $this->recetaVersionId = $recetaVersionId;
        $this->porcionId = $porcionId;
        $this->cantPlanificada = $cantPlanificada;
        $this->cantProducida = $cantProducida;
        $this->mermaGr = $mermaGr;
        $this->estado = $estado;
    }
}