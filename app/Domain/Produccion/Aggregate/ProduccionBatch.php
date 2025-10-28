<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\ValueObjects\Qty;
use DomainException;

class ProduccionBatch
{
    use AggregateRoot;

    /**
     * @var int|null
     */
    public readonly int|null $id;

    /**
     * @var int
     */
    public readonly int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly int $productoId;

    /**
     * @var int
     */
    public readonly int $estacionId;

    /**
     * @var int
     */
    public readonly int $recetaVersionId;

    /**
     * @var int
     */
    public readonly int $porcionId;

    /**
     * @var int
     */
    public readonly int $cantPlanificada;

    /**
     * @var int
     */
    public int $cantProducida;

    /**
     * @var int
     */
    public readonly int $mermaGr;

    /**
     * @var EstadoPlanificado
     */
    public EstadoPlanificado $estado;

    /**
     * @var float
     */
    public float $rendimiento;

    /**
     * @var Qty
     */
    public readonly Qty $qty;

    /**
     * @var int
     */
    public readonly int $posicion;

    /**
     * @var array|null
     */
    public readonly array|null $ruta;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int $productoId
     * @param int $estacionId
     * @param int $recetaVersionId
     * @param int $porcionId
     * @param int $cantPlanificada
     * @param int $cantProducida
     * @param int $mermaGr
     * @param EstadoPlanificado $estado
     * @param float $rendimiento
     * @param Qty $qty
     * @param int $posicion
     * @param array|null $ruta
     */
    public function __construct(
        int|null $id,
        int $ordenProduccionId,
        int $productoId,
        int $estacionId,
        int $recetaVersionId,
        int $porcionId,
        int $cantPlanificada,
        int $cantProducida,
        int $mermaGr,
        EstadoPlanificado $estado,
        float $rendimiento,
        Qty $qty,
        int $posicion,
        array|null $ruta = []
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->productoId = $productoId;
        $this->estacionId = $estacionId;
        $this->recetaVersionId = $recetaVersionId;
        $this->porcionId = $porcionId;
        $this->cantPlanificada = $cantPlanificada;
        $this->cantProducida = $cantProducida;
        $this->mermaGr = $mermaGr;
        $this->estado = $estado;
        $this->rendimiento = $rendimiento;
        $this->qty = $qty;
        $this->posicion = $posicion;
        $this->ruta = $ruta;
    }

    /**
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param int $productoId
     * @param int $estacionId
     * @param int $recetaVersionId
     * @param int $porcionId
     * @param int $cantPlanificada
     * @param int $cantProducida
     * @param int $mermaGr
     * @param EstadoPlanificado $estado
     * @param float $rendimiento
     * @param Qty $qty
     * @param int $posicion
     * @param array $ruta
     * @return ProduccionBatch
     */
    public static function crear(
        int|null $id,
        int $ordenProduccionId,
        int $productoId,
        int $estacionId,
        int $recetaVersionId,
        int $porcionId,
        int $cantPlanificada,
        int $cantProducida,
        int $mermaGr,
        EstadoPlanificado $estado,
        float $rendimiento,
        Qty $qty,
        int $posicion,
        array $ruta
    ): self
    {
        $self = new self(
            $id,
            $ordenProduccionId,
            $productoId,
            $estacionId,
            $recetaVersionId,
            $porcionId,
            $cantPlanificada,
            $cantProducida,
            $mermaGr,
            $estado,
            $rendimiento,
            $qty,
            $posicion,
            $ruta
        );

        $self->record(
            new ProduccionBatchCreado(
                $id,
                $ordenProduccionId,
                $estacionId,
                $qty,
                $posicion
            )
        );

        return $self;
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function procesar(): void
    {
        if (!in_array($this->estado, [EstadoPlanificado::PROGRAMADO], true)) {
            throw new DomainException('No se puede procesar en su estado actual el batch.');
        }

        $this->cantProducida = $this->cantPlanificada;
        $this->estado = EstadoPlanificado::PROCESANDO;
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function despachar(): void
    {
        if (!in_array($this->estado, [EstadoPlanificado::PROCESANDO], true)) {
            throw new DomainException('No se puede despachar en su estado actual el batch.');
        }

        $this->estado = EstadoPlanificado::DESPACHADO;
    }
}