<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ProduccionBatchCreado;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Shared\Aggregate\AggregateRoot;
use App\Domain\Produccion\ValueObjects\Qty;
use DomainException;

/**
 * @class ProduccionBatch
 * @package App\Domain\Produccion\Aggregate
 */
class ProduccionBatch
{
    use AggregateRoot;

    /**
     * @var string|int|null
     */
    public $id;

    /**
     * @var string|int
     */
    public $ordenProduccionId;

    /**
     * @var string|int
     */
    public $productoId;

    /**
     * @var string|int
     */
    public $estacionId;

    /**
     * @var string|int
     */
    public $recetaVersionId;

    /**
     * @var string|int
     */
    public $porcionId;

    /**
     * @var int
     */
    public $cantPlanificada;

    /**
     * @var int
     */
    public $cantProducida;

    /**
     * @var int
     */
    public $mermaGr;

    /**
     * @var EstadoPlanificado
     */
    public $estado;

    /**
     * @var float
     */
    public $rendimiento;

    /**
     * @var Qty
     */
    public $qty;

    /**
     * @var int
     */
    public $posicion;

    /**
     * @var array|null
     */
    public $ruta;

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
