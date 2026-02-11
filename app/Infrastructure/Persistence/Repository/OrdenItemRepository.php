<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\OrderItem as OrdenItemModel;
use App\Domain\Produccion\Repository\OrdenItemRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;

/**
 * @class OrdenItemRepository
 * @package App\Infrastructure\Persistence\Repository
 */
class OrdenItemRepository implements OrdenItemRepositoryInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Constructor
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $id
     * @throws ModelNotFoundException
     * @return OrdenItem|null
     */
    public function byId(string $id): ?OrdenItem
    {
        $row = OrdenItemModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El orden item de produccion id: {$id} no existe.");
        }

        return new OrdenItem(
            $row->id,
            $row->op_id,
            $row->p_id,
            new Qty($row->qty),
            new Sku($row->product->sku),
            $row->price,
            $row->final_price
        );
    }

    /**
     * @param OrdenItem $item
     * @return void
     */
    public function save(OrdenItem $item): void
    {
        if ($item->productId == null) {
            $product = $this->productRepository->bySku($item->sku()->value);
            $item->loadProduct($product);
        }

        OrdenItemModel::updateOrCreate(
            ['id' => $item->id],
            [
                'op_id' => $item->ordenProduccionId,
                'p_id' => $item->productId,
                'qty' => $item->qty()->value,
                'price' => $item->price,
                'final_price' => $item->finalPrice,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
