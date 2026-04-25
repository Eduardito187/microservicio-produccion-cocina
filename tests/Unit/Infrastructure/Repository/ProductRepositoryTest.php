<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Repository;

use App\Domain\Produccion\Entity\Products;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Repository\ProductRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * @class ProductRepositoryTest
 */
class ProductRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $drivers = \PDO::getAvailableDrivers();
        if (! in_array('sqlite', $drivers, true)) {
            $this->markTestSkipped('sqlite PDO driver is required for ProductRepositoryTest.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('sku')->unique();
            $table->string('nombre', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('special_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function test_repository_crud_methods_map_entities_and_throw_when_not_found(): void
    {
        $repository = new ProductRepository;

        $savedId = $repository->save(new Products('prod-1', 'SKU-1', 10.5, 9.5));
        $this->assertSame('prod-1', $savedId);

        $byId = $repository->byId('prod-1');
        $this->assertInstanceOf(Products::class, $byId);
        $this->assertSame('SKU-1', $byId?->sku);

        $bySku = $repository->bySku('SKU-1');
        $this->assertSame('prod-1', $bySku?->id);

        $repository->save(new Products('prod-2', 'SKU-2', 20.0, 19.0));
        $list = $repository->list();
        $this->assertCount(2, $list);

        $repository->delete('prod-2');
        $this->assertCount(1, $repository->list());

        $this->expectException(EntityNotFoundException::class);
        $repository->byId('missing-id');
    }

    public function test_by_sku_throws_when_missing(): void
    {
        $repository = new ProductRepository;

        $this->expectException(EntityNotFoundException::class);
        $repository->bySku('missing-sku');
    }
}
