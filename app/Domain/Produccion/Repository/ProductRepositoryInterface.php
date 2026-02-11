<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\Repository;

use App\Domain\Produccion\Entity\Products;

/**
 * @class ProductRepositoryInterface
 * @package App\Domain\Produccion\Repository
 */
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
     * @return string
     */
    public function save(Products $product): string;

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
