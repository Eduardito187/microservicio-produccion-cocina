<?php

namespace App\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Events\ListaDespachoCreada;
use App\Domain\Produccion\Model\DespachoItems;
use App\Domain\Shared\AggregateRoot;
use DateTimeImmutable;
use DomainException;

class ListaDespacho
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
     * @var DateTimeImmutable
     */
    public DateTimeImmutable $fechaEntrega;

    /**
     * @var int
     */
    public readonly int|string $sucursalId;

    /**
     * @var array|DespachoItems
     */
    private array|DespachoItems $items;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param DateTimeImmutable $fechaEntrega
     * @param int|string $sucursalId
     * @param array|DespachoItems $items
     */
    public function __construct(
        int|null $id,
        int $ordenProduccionId,
        DateTimeImmutable $fechaEntrega,
        int|string $sucursalId,
        array|DespachoItems $items
    ) {
        $this->id = $id;
        $this->ordenProduccionId = $ordenProduccionId;
        $this->fechaEntrega = $fechaEntrega;
        $this->sucursalId = $sucursalId;
        $this->items = $items;
    }

    /**
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param DateTimeImmutable $fechaEntrega
     * @param int|string $sucursalId
     * @param array|DespachoItems $items
     * @return ListaDespacho
     */
    public static function crear(
        int|null $id,
        int $ordenProduccionId,
        DateTimeImmutable $fechaEntrega,
        int|string $sucursalId,
        array|DespachoItems $items
    ): self
    {
        $self = new self(
            $id,
            $ordenProduccionId,
            $fechaEntrega,
            $sucursalId,
            $items
        );

        $self->record(new ListaDespachoCreada($id, $fechaEntrega));

        return $self;
    }

    /**
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param DateTimeImmutable $fechaEntrega
     * @param int|string $sucursalId
     * @param array|DespachoItems $items
     * @return ListaDespacho
     */
    public static function reconstitute(
        int|null $id,
        int $ordenProduccionId,
        DateTimeImmutable $fechaEntrega,
        int|string $sucursalId,
        array|DespachoItems $items
    ): self
    {
        $self = new self(
            $id,
            $ordenProduccionId,
            $fechaEntrega,
            $sucursalId,
            $items
        );

        return $self;
    }

    /**
     * @param DespachoItems|array $nuevos
     * @throws DomainException
     * @return void
     */
    public function replaceItems(DespachoItems|array $nuevos): void
    {
        $this->items = $nuevos;
    }

    /**
     * @return int|null
     */
    public function id(): int|null
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function ordenProduccionId(): int
    {
        return $this->ordenProduccionId;
    }

    /**
     * @return string|DateTimeImmutable
     */
    public function fechaEntrega(): string|DateTimeImmutable
    {
        return $this->fechaEntrega;
    }

    /**
     * @return int|string
     */
    public function sucursalId(): int|string
    {
        return $this->sucursalId;
    }

    /**
     * @return DespachoItems|array
     */
    public function items(): DespachoItems|array
    {
        return $this->items;
    }
}