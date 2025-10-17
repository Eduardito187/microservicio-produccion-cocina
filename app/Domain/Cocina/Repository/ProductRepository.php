<?php

namespace App\Domain\Cocina\Repository;

use App\Domain\Cocina\Aggregate\Products;

interface ProductRepository
{
    /**
     * @param string $id
     * @return Products|null
     */
    public function byId(string $id): ? Products;

    /**
     * @param Products $product
     * @return void
     */
    public function save(Products $product): void;
}