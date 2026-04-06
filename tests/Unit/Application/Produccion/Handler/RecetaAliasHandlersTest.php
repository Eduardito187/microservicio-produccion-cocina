<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Handler;

use App\Application\Produccion\Command\ActualizarReceta;
use App\Application\Produccion\Command\CrearReceta;
use App\Application\Produccion\Command\EliminarReceta;
use App\Application\Produccion\Command\ListarRecetas;
use App\Application\Produccion\Command\VerReceta;
use App\Application\Produccion\Handler\ActualizarRecetaHandler;
use App\Application\Produccion\Handler\CrearRecetaHandler;
use App\Application\Produccion\Handler\EliminarRecetaHandler;
use App\Application\Produccion\Handler\ListarRecetasHandler;
use App\Application\Produccion\Handler\VerRecetaHandler;
use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @class RecetaAliasHandlersTest
 */
class RecetaAliasHandlersTest extends TestCase
{
    public function test_crear_receta_handler_alias_ejecuta_flujo_completo(): void
    {
        $repository = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repository->expects($this->once())->method('save')->willReturn('rec-1');

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $tx = $this->createMock(TransactionAggregate::class);
        $tx->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new CrearRecetaHandler($repository, $tx, $publisher);
        $id = $handler(new CrearReceta('Receta A', ['kcal' => 300], [['agua']], 'desc', 'inst', 300));

        $this->assertSame('rec-1', $id);
    }

    public function test_actualizar_receta_handler_alias_ejecuta_flujo_completo(): void
    {
        $repository = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->willReturn(
            new RecetaVersion('rec-2', 'Vieja', [], [], null, null, null)
        );
        $repository->expects($this->once())->method('save')->willReturn('rec-2');

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $tx = $this->createMock(TransactionAggregate::class);
        $tx->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new ActualizarRecetaHandler($repository, $tx, $publisher);
        $id = $handler(new ActualizarReceta('rec-2', 'Nueva', ['kcal' => 350], [['arroz']], 'desc', 'inst', 350));

        $this->assertSame('rec-2', $id);
    }

    public function test_eliminar_receta_handler_alias_ejecuta_flujo_completo(): void
    {
        $repository = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->willReturn(
            new RecetaVersion('rec-3', 'Receta', [], [], null, null, null)
        );
        $repository->expects($this->once())->method('delete')->with('rec-3');

        $tx = $this->createMock(TransactionAggregate::class);
        $tx->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new EliminarRecetaHandler($repository, $tx);
        $handler(new EliminarReceta('rec-3'));

        $this->assertTrue(true);
    }

    public function test_listar_recetas_handler_alias_mapea_resultados(): void
    {
        $repository = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repository->expects($this->once())->method('list')->willReturn([
            new RecetaVersion('rec-4', 'Receta 4', ['kcal' => 500], [['pollo']], 'desc', 'inst', 500),
        ]);

        $tx = $this->createMock(TransactionAggregate::class);
        $tx->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new ListarRecetasHandler($repository, $tx);
        $rows = $handler(new ListarRecetas);

        $this->assertSame('rec-4', $rows[0]['id']);
    }

    public function test_ver_receta_handler_alias_mapea_resultado(): void
    {
        $repository = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->with('rec-5')->willReturn(
            new RecetaVersion('rec-5', 'Receta 5', ['kcal' => 600], [['pescado']], 'desc', 'inst', 600)
        );

        $tx = $this->createMock(TransactionAggregate::class);
        $tx->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new VerRecetaHandler($repository, $tx);
        $row = $handler(new VerReceta('rec-5'));

        $this->assertSame('rec-5', $row['id']);
        $this->assertSame('Receta 5', $row['nombre']);
    }
}
