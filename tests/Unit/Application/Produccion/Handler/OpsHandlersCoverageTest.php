<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Handler;

use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Command\ListarPorciones;
use App\Application\Produccion\Command\PlanificarOP;
use App\Application\Produccion\Command\ProcesadorOP;
use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Application\Produccion\Handler\ListarPorcionesHandler;
use App\Application\Produccion\Handler\PlanificadorOPHandler;
use App\Application\Produccion\Handler\ProcesadorOPHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Entity\Porcion;
use App\Domain\Produccion\Enum\EstadoOP;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Domain\Produccion\Repository\PorcionRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @class OpsHandlersCoverageTest
 */
class OpsHandlersCoverageTest extends TestCase
{
    public function test_planificador_op_handler_genera_batches_planifica_y_guarda(): void
    {
        $orden = OrdenProduccion::crear(new DateTimeImmutable('2026-01-01'), [], [], [], 'op-plan-1');

        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->with('op-plan-1')->willReturn($orden);
        $repository->expects($this->once())->method('save')->with($orden)->willReturn('op-plan-1');

        $transactionAggregate = $this->createMock(TransactionAggregate::class);
        $transactionAggregate->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new PlanificadorOPHandler($repository, $transactionAggregate);
        $result = $handler(new PlanificarOP([
            'ordenProduccionId' => 'op-plan-1',
            'porcionId' => 10,
        ]));

        $this->assertSame('op-plan-1', $result);
    }

    public function test_procesador_op_handler_procesa_y_guarda(): void
    {
        $orden = OrdenProduccion::reconstitute(
            'op-proc-1',
            new DateTimeImmutable('2026-01-01'),
            EstadoOP::PLANIFICADA,
            [],
            [],
            []
        );

        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->with('op-proc-1')->willReturn($orden);
        $repository->expects($this->once())->method('save')->with($orden)->willReturn('op-proc-1');

        $transactionAggregate = $this->createMock(TransactionAggregate::class);
        $transactionAggregate->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new ProcesadorOPHandler($repository, $transactionAggregate);
        $result = $handler(new ProcesadorOP('op-proc-1'));

        $this->assertSame('op-proc-1', $result);
    }

    public function test_despachador_op_handler_despacha_cierra_y_guarda(): void
    {
        $orden = OrdenProduccion::reconstitute(
            'op-desp-1',
            new DateTimeImmutable('2026-01-01'),
            EstadoOP::EN_PROCESO,
            [],
            [],
            []
        );

        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->expects($this->once())->method('byId')->with('op-desp-1')->willReturn($orden);
        $repository->expects($this->once())->method('save')->with($orden)->willReturn('op-desp-1');

        $transactionAggregate = $this->createMock(TransactionAggregate::class);
        $transactionAggregate->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new DespachadorOPHandler($repository, $transactionAggregate);
        $result = $handler(new DespachadorOP([
            'ordenProduccionId' => 'op-desp-1',
            'itemsDespacho' => [['sku' => 'SKU-1', 'qty' => 1]],
            'pacienteId' => 'pac-1',
            'direccionId' => 'dir-1',
            'ventanaEntrega' => 'ven-1',
        ]));

        $this->assertSame('op-desp-1', $result);
    }

    public function test_listar_porciones_handler_mapea_entidades(): void
    {
        $repository = $this->createMock(PorcionRepositoryInterface::class);
        $repository->expects($this->once())->method('list')->willReturn([
            new Porcion('por-1', 'Normal', 350),
            new Porcion('por-2', 'Ligera', 250),
        ]);

        $transactionAggregate = $this->createMock(TransactionAggregate::class);
        $transactionAggregate->expects($this->once())->method('runTransaction')
            ->willReturnCallback(fn (callable $callback) => $callback());

        $handler = new ListarPorcionesHandler($repository, $transactionAggregate);
        $result = $handler(new ListarPorciones);

        $this->assertSame([
            ['id' => 'por-1', 'nombre' => 'Normal', 'peso_gr' => 350],
            ['id' => 'por-2', 'nombre' => 'Ligera', 'peso_gr' => 250],
        ], $result);
    }
}
