<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\OrdenProduccionCreada;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;
use DomainException;

class OrdenProduccion
{
    use AggregateRoot;

    /**
     * @var string
     */
    public readonly string $id;
    /**
     * @var DateTimeImmutable
     */
    public readonly DateTimeImmutable $fecha;
    /**
     * @var string
     */
    public readonly string $sucursalId;
    /**
     * @var string
     */
    private $estado;

    /**
     * Constructor
     * 
     * @param string $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @param string $estado
     */
    public function __construct(
        string $id,
        DateTimeImmutable $fecha,
        string $sucursalId,
        string $estado = 'CREADA',
    ) {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->sucursalId = $sucursalId;
        $this->estado = $estado;
    }

    /**
     * @param string $id
     * @param DateTimeImmutable $fecha
     * @param string $sucursalId
     * @return OrdenProduccion
     */
    public static function crear(string $id, DateTimeImmutable $fecha, string $sucursalId): self
    {
        $self = new self($id, $fecha, $sucursalId);

        $self->record(new OrdenProduccionCreada(
            $id,
            $fecha->format('Y-m-d'),
            $sucursalId
        ));

        return $self;
    }

    /**
     * @throws DomainException
     * @return void
     */
    public function cerrar(): void
    {
        if ($this->estado !== 'EN_PROCESO' && $this->estado !== 'CREADA') {
            throw new DomainException('OP no cerrable');
        }

        $this->estado = 'CERRADA';
        // $this->record(new OrdenProduccionCerrada($this->id));
    }

    /**
     * @return string
     */
    public function estado(): string
    {
        return $this->estado;
    }
}
