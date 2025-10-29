<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Model\Product as ProductModel;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Domain\Produccion\Entity\Products;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @param string $id
     * @throws ModelNotFoundException
     * @return Products|null
     */
    public function byId(string $id): ?Products
    {
        $row = ProductModel::find($id);

        if (!$row) {
            throw new ModelNotFoundException("El producto id: {$id} no existe.");
        }

        return new Products(
            $row->id,
            $row->sku,
            $row->price,
            $row->special_price
        );
    }

    /**
     * @param string $sku
     * @return Products|null
     */
    public function bySku(string $sku): ?Products
    {
        $row = ProductModel::where('sku', $sku)->first();

        if (!$row) {
            throw new ModelNotFoundException("El producto sku: {$sku} no existe.");
        }

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