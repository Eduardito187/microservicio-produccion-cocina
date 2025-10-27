<?php

namespace App\Domain\Produccion\Aggregate;

use App\Infrastructure\Persistence\Repository\ListaDespachoRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Events\ListaDespachoCreada;
use App\Domain\Produccion\Model\DespachoItems;
use App\Domain\Shared\Aggregate\AggregateRoot;
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
     * @var ListaDespachoRepository
     */
    public readonly ListaDespachoRepository $listaDespachoRepository;

    /**
     * Constructor
     * 
     * @param int|null $id
     * @param int $ordenProduccionId
     * @param DateTimeImmutable $fechaEntrega
     * @param int|string $sucursalId
     * @param array|DespachoItems $items
     * @throws ModelNotFoundException
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
        $this->listaDespachoRepository = app(ListaDespachoRepository::class);
        $this->listaDespachoRepository->validToCreate($this->ordenProduccionId);
    }

    /**
     * @param int $ordenProduccionId
     * @param DateTimeImmutable $fechaEntrega
     * @param int|string $sucursalId
     * @param array|DespachoItems $items
     * @param int|null $id
     * @return ListaDespacho
     */
    public static function crear(
        int $ordenProduccionId,
        DateTimeImmutable $fechaEntrega,
        int|string $sucursalId,
        array|DespachoItems $items = [],
        int|null $id = null
    ): self {
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
    ): self {
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