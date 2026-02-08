<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\ValueObjects\Qty;
use DomainException;

class ProduccionBatch
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    public readonly string|int|null $id;

    /**
     * @var int
     */
    public readonly string|int $ordenProduccionId;

    /**
     * @var int
     */
    public readonly string|int $productoId;

    /**
     * @var int
     */
    public readonly string|int $estacionId;

    /**
     * @var int
     */
    public readonly string|int $recetaVersionId;

    /**
     * @var int
     */
    public readonly string|int $porcionId;

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
     * @param string|int|null $id
     * @param string|int $ordenProduccionId
     * @param string|int $productoId
     * @param string|int $estacionId
     * @param string|int $recetaVersionId
     * @param string|int $porcionId
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
        string|int|null $id,
        string|int $ordenProduccionId,
        string|int $productoId,
        string|int $estacionId,
        string|int $recetaVersionId,
        string|int $porcionId,
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
     * @param string|int|null $id
     * @param string|int $ordenProduccionId
     * @param string|int $productoId
     * @param string|int $estacionId
     * @param string|int $recetaVersionId
     * @param string|int $porcionId
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
        string|int|null $id,
        string|int $ordenProduccionId,
        string|int $productoId,
        string|int $estacionId,
        string|int $recetaVersionId,
        string|int $porcionId,
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
                $productoId,
                $recetaVersionId,
                $porcionId,
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
