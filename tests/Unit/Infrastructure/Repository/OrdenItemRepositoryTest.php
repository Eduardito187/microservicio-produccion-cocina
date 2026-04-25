<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Repository;

use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Entity\Products;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Infrastructure\Persistence\Repository\ProductRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * @class OrdenItemRepositoryTest
 */
class OrdenItemRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $drivers = \PDO::getAvailableDrivers();
        if (! in_array('sqlite', $drivers, true)) {
            $this->markTestSkipped('sqlite PDO driver is required for OrdenItemRepositoryTest.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('order_item');
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('sku')->unique();
            $table->string('nombre', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('special_price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('order_item', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('op_id')->nullable();
            $table->string('p_id')->nullable();
            $table->integer('qty');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function test_save_loads_product_by_sku_when_product_id_is_null(): void
    {
        $productRepository = new ProductRepository;
        $productRepository->save(new Products('prod-1', 'SKU-1', 19.5, 17.2));

        $repository = new OrdenItemRepository($productRepository);
        $item = new OrdenItem(
            'item-1',
            'op-1',
            null,
            new Qty(3),
            new Sku('SKU-1')
        );

        $repository->save($item);

        $row = DB::table('order_item')->where('id', 'item-1')->first();
        $this->assertNotNull($row);
        $this->assertSame('prod-1', $row->p_id);
        $this->assertSame(19.5, (float) $row->price);
        $this->assertSame(17.2, (float) $row->final_price);
    }

    public function test_save_keeps_explicit_product_id_without_product_lookup(): void
    {
        $repository = new OrdenItemRepository(new ProductRepository);

        $item = new OrdenItem(
            'item-2',
            'op-2',
            'prod-explicit',
            new Qty(1),
            new Sku('SKU-NOT-FOUND'),
            12.0,
            11.0
        );

        $repository->save($item);

        $row = DB::table('order_item')->where('id', 'item-2')->first();
        $this->assertNotNull($row);
        $this->assertSame('prod-explicit', $row->p_id);
        $this->assertSame(12.0, (float) $row->price);
        $this->assertSame(11.0, (float) $row->final_price);
    }

    public function test_by_id_maps_entity_and_throws_when_missing(): void
    {
        DB::table('products')->insert([
            'id' => 'prod-9',
            'sku' => 'SKU-9',
            'price' => 20,
            'special_price' => 18,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_item')->insert([
            'id' => 'item-9',
            'op_id' => 'op-9',
            'p_id' => 'prod-9',
            'qty' => 4,
            'price' => 20,
            'final_price' => 18,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $repository = new OrdenItemRepository(new ProductRepository);
        $entity = $repository->byId('item-9');

        $this->assertInstanceOf(OrdenItem::class, $entity);
        $this->assertSame('item-9', $entity?->id);
        $this->assertSame('op-9', $entity?->ordenProduccionId);
        $this->assertSame(4, $entity?->qty()->value);
        $this->assertSame('SKU-9', $entity?->sku()->value);

        $this->expectException(EntityNotFoundException::class);
        $repository->byId('missing-item');
    }
}
