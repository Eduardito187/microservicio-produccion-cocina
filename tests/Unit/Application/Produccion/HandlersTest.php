<?php
/**
 * Microservicio "Produccion y Cocina"
 */

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

/**
 * @class HandlersTest
 * @package Tests\Unit\Application\Produccion
 */
class HandlersTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function transactionAggregate(): TransactionAggregate
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
     * @return void
     */
    public function test_generar_op_handler_crea_op_y_persiste(): void
    {
        $orderId = 'e28e9cc2-5225-40c0-b88b-2341f96d76a3';
        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (OrdenProduccion $ordenProduccion): bool {
                return $ordenProduccion->estado() === EstadoOP::CREADA && $ordenProduccion->sucursalId() === 'SCZ-001' && count($ordenProduccion->items()) === 2;
            }))->willReturn($orderId);
        $handler = new GenerarOPHandler($repository, $this->transactionAggregate());
        $command = new GenerarOP(
            $orderId,
            new DateTimeImmutable('2025-11-04'),
            'SCZ-001',
            [['sku' => 'PIZZA-PEP', 'qty' => 1], ['sku' => 'PIZZA-MARG', 'qty' => 2]]
        );
        $result = $handler($command);

        $this->assertSame($orderId, $result);
    }

    /**
     * @return void
     */
    public function test_planificar_procesar_y_despachar_handlers_ejecutan_transiciones(): void
    {
        $orderId = '2fbd6b2a-462d-4a9a-a22f-efc7c83ec4a5';
        $productId = 'd2c3b4a5-1f3c-4b2f-9f54-7ab02d1b33c9';
        $estacionId = '9b7b5fbe-6b65-4d1d-8fdd-52f143b2552f';
        $recetaVersionId = 'f7a1e0b2-2c4d-4c0a-9b8e-0a4b2f9d8f7a';
        $porcionId = '1d6d5e54-e8f7-4e5e-9f9d-247d8c6c8c8d';
        $pacienteId = '6c3c2b12-8e50-4d3e-9f5e-96b58c7b9c17';
        $direccionId = 'a19f3b2a-86b5-4a3d-9fb1-7b332f232a3b';
        $ventanaEntregaId = 'bb2b9b6c-1c0f-4f37-9f10-0d8d6d4e1454';
        $ordenProduccion = OrdenProduccion::reconstitute($orderId, new DateTimeImmutable('2025-11-04'), 'SCZ-001', EstadoOP::CREADA, [], [], []);
        $ordenProduccion->agregarItems([['sku' => 'PIZZA-PEP', 'qty' => 1]]);

        foreach ($ordenProduccion->items() as $item) {
            $item->loadProduct(new Products($productId, 'PIZZA-PEP', 10.0, 0.0));
        }

        $repository = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $repository->method('byId')->willReturn($ordenProduccion);
        $repository->method('save')->willReturn($orderId);

        $tx = $this->transactionAggregate();
        $planificar = new PlanificadorOPHandler($repository, $tx);
        $planificar(new PlanificarOP([
            "ordenProduccionId" => $orderId,
            "estacionId" => $estacionId,
            "recetaVersionId" => $recetaVersionId,
            "porcionId" => $porcionId
        ]));

        $this->assertSame(EstadoOP::PLANIFICADA, $ordenProduccion->estado());
        $procesar = new ProcesadorOPHandler($repository, $tx);
        $procesar(new ProcesadorOP(opId: $orderId));

        $this->assertSame(EstadoOP::EN_PROCESO, $ordenProduccion->estado());
        $despachar = new DespachadorOPHandler($repository, $tx);
        $despachar(new DespachadorOP(
            [
                "ordenProduccionId" => $orderId,
                "itemsDespacho" => [
                    ['sku' => 'PIZZA-PEP', 'recetaVersionId' => $recetaVersionId]
                ],
                "pacienteId" => $pacienteId,
                "direccionId" => $direccionId,
                "ventanaEntrega" => $ventanaEntregaId
            ]
        ));

        $this->assertSame(EstadoOP::CERRADA, $ordenProduccion->estado());
    }
}
