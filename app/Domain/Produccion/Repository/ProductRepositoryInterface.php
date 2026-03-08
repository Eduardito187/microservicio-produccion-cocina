<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Products;

/**
 * @class ProductRepositoryInterface
 */
interface ProductRepositoryInterface
{
    public function byId(string $id): ?Products;

    public function bySku(string $sku): ?Products;

    public function save(Products $product): string;

    /**
     * @return Products[]
     */
    public function list(): array;

    public function delete(string|int $id): void;
}
