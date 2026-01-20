<?php

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Produccion\Handler\PlanificadorOPHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Handler\DespachadorOPHandler;
use App\Application\Produccion\Handler\ProcesadorOPHandler;
use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Application\Produccion\Command\DespachadorOP;
use App\Application\Produccion\Command\PlanificarOP;
use App\Application\Produccion\Command\ProcesadorOP;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Application\Produccion\Command\GenerarOP;
use App\Domain\Produccion\Enum\EstadoOP;
use App\Domain\Produccion\Entity\Products;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class HandlersTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function transactionAggregate(): TransactionAggregate
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
    public function test_generar_op_handler_crea_op_y_persiste(): void
    {
        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (OrdenProduccion $ordenProduccion): bool {
                return $ordenProduccion->estado() === EstadoOP::CREADA && $ordenProduccion->sucursalId() === 'SCZ-001' && count($ordenProduccion->items()) === 2;
            }))->willReturn(123);
        $handler = new GenerarOPHandler($repository, $this->transactionAggregate());
        $command = new GenerarOP(
            123,
            new DateTimeImmutable('2025-11-04'),
            'SCZ-001',
            [['sku' => 'PIZZA-PEP', 'qty' => 1], ['sku' => 'PIZZA-MARG', 'qty' => 2]]
        );
        $result = $handler($command);

        $this->assertSame(123, $result);
    }

    /**
     * @return void
     */
    public function test_planificar_procesar_y_despachar_handlers_ejecutan_transiciones(): void
    {
        $ordenProduccion = OrdenProduccion::reconstitute(123, new DateTimeImmutable('2025-11-04'), 'SCZ-001', EstadoOP::CREADA, [], [], []);
        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 1]]);

        foreach ($ordenProduccion->items() as $item) {
            $item->loadProduct(new Products(1, 'PIZZA-PEP', 10.0, 0.0));
        }

        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->method('byId')->willReturn($ordenProduccion);
        $repository->method('save')->willReturn(123);

        $tx = $this->transactionAggregate();
        $planificar = new PlanificadorOPHandler($repository, $tx);
        $planificar(new PlanificarOP(["ordenProduccionId" => 123, "estacionId" => 1, "recetaVersionId" => 1, "porcionId" => 1]));

        $this->assertSame(EstadoOP::PLANIFICADA, $ordenProduccion->estado());
        $procesar = new ProcesadorOPHandler($repository, $tx);
        $procesar(new ProcesadorOP(opId: 123));

        $this->assertSame(EstadoOP::EN_PROCESO, $ordenProduccion->estado());
        $despachar = new DespachadorOPHandler($repository, $tx);
        $despachar(new DespachadorOP(
            [
                "ordenProduccionId" => 123,
                "itemsDespacho" => [
                    ['sku' => 'PIZZA-PEP', 'recetaVersionId' => 1]
                ],
                "pacienteId" => 1,
                "direccionId" => 1,
                "ventanaEntrega" => 1
            ]
        ));

        $this->assertSame(EstadoOP::CERRADA, $ordenProduccion->estado());
    }
}