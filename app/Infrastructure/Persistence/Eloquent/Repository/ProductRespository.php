<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Infrastructure\Persistence\Eloquent\Model\Products as ProductModel;
use App\Domain\Cocina\Repository\ProductRepository as DomainRepository;
use App\Domain\Cocina\Aggregate\Products;

class ProductRespository implements DomainRepository
{
    /**
     * @param string $id
     * @return Products|null
     */
    public function byId(string $id): ?Products
    {
        $row = ProductModel::find($id);

        if (!$row) return null;

        return new Products(
            $row->id,
            $row->sku,
            $row->price,
            $row->special_price
        );
    }

    /**
     * @param Products $product
     * @return void
     */
    public function save(Products $product): void
    {
        ProductModel::updateOrCreate(
            ['id' => $product->id],
            ['sku' => $product->sku, 'price' => $product->price, 'special_price' => $product->special_price]
        );
    }
}