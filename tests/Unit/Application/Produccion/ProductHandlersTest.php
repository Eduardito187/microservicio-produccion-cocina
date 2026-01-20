<?php

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
use PHPUnit\Framework\TestCase;

class ProductHandlersTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @return void
     */
    public function test_crear_producto_persiste_y_devuelve_id_por_sku(): void
    {
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Products $product): bool {
                return $product->id === null && $product->sku === 'PIZZA-PEP'
                    && $product->price === 100.0 && $product->special_price === 80.0;
            }));
        $repository->expects($this->once())->method('bySku')
            ->with('PIZZA-PEP')->willReturn(new Products(id: 99, sku: 'PIZZA-PEP', price: 100.0, special_price: 80.0));
        $handler = new CrearProductoHandler($repository, $this->tx());
        $id = $handler(new CrearProducto('PIZZA-PEP', 100.0, 80.0));

        $this->assertSame(99, $id);
    }

    /**
     * @return void
     */
    public function test_actualizar_producto_valida_existencia_y_persiste(): void
    {
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')
            ->with('10')->willReturn(new Products(id: 10, sku: 'SKU-OLD', price: 1.0, special_price: 0.0));
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Products $product): bool {
                return $product->id === 10 && $product->sku === 'SKU-NEW'
                    && $product->price === 200.0 && $product->special_price === 0.0;
            }));
        $handler = new ActualizarProductoHandler($repository, $this->tx());
        $id = $handler(new ActualizarProducto(10, 'SKU-NEW', 200.0, 0.0));

        $this->assertSame(10, $id);
    }

    /**
     * @return void
     */
    public function test_ver_y_listar_producto_mapean_campos(): void
    {
        $product = new Products(id: 7, sku: 'SKU-007', price: 50.0, special_price: 0.0);

        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('byId')->with('7')->willReturn($product);
        $ver = new VerProductoHandler($repository, $this->tx());
        $data = $ver(new VerProducto(7));
        $this->assertSame(['id' => 7, 'sku' => 'SKU-007', 'price' => 50.0, 'special_price' => 0.0], $data);

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
        $repository = $this->createMock(ProductRepositoryInterface::class);
        $repository->method('byId')->with('5')->willReturn(new Products(id: 5, sku: 'SKU-005', price: 1.0, special_price: 0.0));
        $repository->expects($this->once())->method('delete')->with(5);
        $handler = new EliminarProductoHandler($repository, $this->tx());
        $handler(new EliminarProducto(5));

        $this->assertTrue(true);
    }
}
