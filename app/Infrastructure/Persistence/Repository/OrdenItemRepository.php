<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\OrdenItem as OrdenItemModel;
use App\Domain\Produccion\Aggregate\OrdenItem as AggregateOrdenItem;
use App\Domain\Produccion\Repository\OrdenItemRepositoryInterface;
use App\Infrastructure\Persistence\Repository\ProductRepository;

class OrdenItemRepository implements OrdenItemRepositoryInterface
{
    /**
     * @var ProductRepository
     */
    public readonly ProductRepository $productRepository;

    /**
     * Constructor
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $id
     * @return AggregateOrdenItem|null
     */
    public function byId(string $id): ?AggregateOrdenItem
    {
        $row = OrdenItemModel::find($id);

        if (!$row) return null;

        return new AggregateOrdenItem(
            $row->id,
            $row->ordenProduccionId,
            $row->productId,
            $row->sku,
            $row->qty,
            $row->price,
            $row->finalPrice
        );
    }

    /**
     * @param AggregateOrdenItem $item
     * @return void
     */
    public function save(AggregateOrdenItem $item): void
    {
        $product = $this->productRepository->bySku($item->sku);
        $item->loadProduct($product);
        OrdenItemModel::updateOrCreate(
            ['id' => $item->id],
            [
                'op_id' => $item->ordenProduccionId,
                'p_id' => $item->productId,
                'sku' => $item->sku,
                'qty' => $item->qty,
                'price' => $item->price,
                'final_price' => $item->finalPrice,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}