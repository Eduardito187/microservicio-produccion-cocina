<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Produccion\Handler\ActualizarProductoHandler;
use App\Domain\Produccion\Repository\ProductRepositoryInterface;
use App\Application\Produccion\Handler\EliminarProductoHandler;
use App\Application\Produccion\Handler\ListarProductosHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Handler\CrearProductoHandler;
use App\Application\Produccion\Handler\VerProductoHandler;
use App\Application\Produccion\Command\ActualizarProducto;
use App\Application\Produccion\Command\EliminarProducto;
use App\Application\Produccion\Command\ListarProductos;
use App\Application\Produccion\Command\CrearProducto;
use App\Application\Produccion\Command\VerProducto;
use App\Domain\Produccion\Entity\Products;
use App\Application\Shared\DomainEventPublisherInterface;
use PHPUnit\Framework\TestCase;

/**
 * @class ProductHandlersTest
 * @package Tests\Unit\Application\Produccion
 */
class ProductHandlersTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            /**
             * @param callable $callback
             * @return mixed
             */
            public function run(callable $callback): mixed {
                return $callback();
            }

            /**
             * @param callable $callback): void {}
        };

        return new TransactionAggregate( $transactionManager
             * @return mixed
             */
            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @return DomainEventPublisherInterface
     */
    private function eventPublisher(): DomainEventPublisherInterface
    {
        return $this->createMock(DomainEventPublisherInterface::class);
    }

    /**
     * @return void
     */
    public function test_crear_producto_persiste_y_devuelve_id_por_sku(): void
    {
        $productId = 'e28e9cc2-5225-40c0-b88b-2341f96d76a3';
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Products $product): bool {
                return $product->id === null && $product->sku === 'PIZZA-PEP'
                    && $product->price === 100.0 && $product->special_price === 80.0;
            }))->willReturn($productId);
        $handler = new CrearProductoHandler($repository, $this->tx(), $this->eventPublisher());
        $id = $handler(new CrearProducto('PIZZA-PEP', 100.0, 80.0));

        $this->assertSame($productId, $id);
    }

    /**
     * @return void
     */
    public function test_actualizar_producto_valida_existencia_y_persiste(): void
    {
        $productId = '0d61b6de-30b1-45db-9f52-b5c1e3e3f1c3';
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')
            ->with($productId)->willReturn(new Products(id: $productId, sku: 'SKU-OLD', price: 1.0, special_price: 0.0));
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Products $product) use ($productId): bool {
                return $product->id === $productId && $product->sku === 'SKU-NEW'
                    && $product->price === 200.0 && $product->special_price === 0.0;
            }))->willReturn($productId);
        $handler = new ActualizarProductoHandler($repository, $this->tx(), $this->eventPublisher());
        $id = $handler(new ActualizarProducto($productId, 'SKU-NEW', 200.0, 0.0));

        $this->assertSame($productId, $id);
    }

    /**
     * @return void
     */
    public function test_ver_y_listar_producto_mapean_campos(): void
    {
        $productId = '2fbd6b2a-462d-4a9a-a22f-efc7c83ec4a5';
        $product = new Products(id: $productId, sku: 'SKU-007', price: 50.0, special_price: 0.0);

        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('byId')->with($productId)->willReturn($product);
        $ver = new VerProductoHandler($repository, $this->tx());
        $data = $ver(new VerProducto($productId));
        $this->assertSame(['id' => $productId, 'sku' => 'SKU-007', 'price' => 50.0, 'special_price' => 0.0], $data);

        $repository2 = $this->createMock(ProductRepositoryInterface::class);
        $repository2->method('list')->willReturn([$product]);
        $listar = new ListarProductosHandler($repository2, $this->tx());
        $list = $listar(new ListarProductos());

        $this->assertCount(1, $list);
        $this->assertSame('SKU-007', $list[0]['sku']);
    }

    /**
     * @return void
     */
    public function test_eliminar_producto_invoca_delete(): void
    {
        $productId = '5b7dcd16-c4e5-455c-bad2-2be581bfc0f9';
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('byId')->with($productId)->willReturn(new Products(id: $productId, sku: 'SKU-005', price: 1.0, special_price: 0.0));
        $repository->expects($this->once())->method('delete')->with($productId);
        $handler = new EliminarProductoHandler($repository, $this->tx());
        $handler(new EliminarProducto($productId));

        $this->assertTrue(true);
    }
}
