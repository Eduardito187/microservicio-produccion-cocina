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
     * @inheritDoc
     */
    private function transactionAggregate(): TransactionAggregate
    {
        $tm = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void
            {
                // En unit tests no ejecutamos lógica post-commit.
                // Si en el futuro se requiere, se puede invocar aquí.
            }
        };

        return new TransactionAggregate($tm);
    }

    /**
     * @inheritDoc
     */
    public function test_generar_op_handler_crea_op_y_persiste(): void
    {
        $repo = $this->createMock(OrdenProduccionRepositoryInterface::class);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (OrdenProduccion $op): bool {
                return $op->estado() === EstadoOP::CREADA
                    && $op->sucursalId() === 'SCZ-001'
                    && count($op->items()) === 2;
            }))
            ->willReturn(123);

        $handler = new GenerarOPHandler($repo, $this->transactionAggregate());

        $command = new GenerarOP(
            123,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            items: [
                ['sku' => 'PIZZA-PEP', 'qty' => 1],
                ['sku' => 'PIZZA-MARG', 'qty' => 2],
            ]
        );

        $result = $handler($command);
        $this->assertSame(123, $result);
    }

    /**
     * @inheritDoc
     */
    public function test_planificar_procesar_y_despachar_handlers_ejecutan_transiciones(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 123,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::CREADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $op->agregarItems([
            ['sku' => 'PIZZA-PEP', 'qty' => 1],
        ]);

        // ✅ FIX: cargar producto en los items antes de planificar (evita productoId=null)
        foreach ($op->items() as $item) {
            $item->loadProduct(new Products(
                id: 1,
                sku: 'PIZZA-PEP',
                price: 10.0,
                special_price: 0.0
            ));
        }

        $repo = $this->createMock(OrdenProduccionRepositoryInterface::class);

        // byId siempre devuelve el mismo agregado (para simplificar el test)
        $repo->method('byId')->willReturn($op);
        $repo->method('save')->willReturn(123);

        $tx = $this->transactionAggregate();

        $planificar = new PlanificadorOPHandler($repo, $tx);
        $planificar(new PlanificarOP(
            [
                "ordenProduccionId" => 123,
                "estacionId" => 1,
                "recetaVersionId" => 1,
                "porcionId" => 1
            ]
        ));
        $this->assertSame(EstadoOP::PLANIFICADA, $op->estado());

        $procesar = new ProcesadorOPHandler($repo, $tx);
        $procesar(new ProcesadorOP(opId: 123));
        $this->assertSame(EstadoOP::EN_PROCESO, $op->estado());

        $despachar = new DespachadorOPHandler($repo, $tx);
        $despachar(new DespachadorOP(
            [
                "ordenProduccionId" => 123,
                "itemsDespacho" => [
                    [
                        'sku' => 'PIZZA-PEP',
                        'recetaVersionId' => 1
                    ]
                ],
                "pacienteId" => 1,
                "direccionId" => 1,
                "ventanaEntrega" => 1
            ]
        ));
        $this->assertSame(EstadoOP::CERRADA, $op->estado());
    }
}