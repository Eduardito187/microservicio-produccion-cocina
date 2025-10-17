<?php

namespace App\Domain\Cocina\Repository;

use App\Domain\Cocina\Aggregate\Products;

interface ProductRepositoryInterface
{
    /**
     * @param string $id
     * @return Products|null
     */
    public function byId(string $id): ? Products;

    /**
     * @param string $sku
     * @return Products|null
     */
    public function bySku(string $sku): ?Products;

    /**
     * @param Products $product
     * @return void
     */
    public function save(Products $product): void;
}