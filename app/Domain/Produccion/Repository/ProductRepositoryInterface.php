<?php

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Products;

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

    /**
     * @return Products[]
     */
    public function list(): array;

    /**
     * @param string|int $id
     * @return void
     */
    public function delete(string|int $id): void;
}
