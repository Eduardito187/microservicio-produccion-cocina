<?php

namespace Tests\Unit\Application\Produccion\Handler;

use App\Domain\Produccion\Repository\OrdenProduccionRepositoryInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Handler\GenerarOPHandler;
use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Application\Produccion\Command\GenerarOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class GenerarOPHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function test_handler_crea_y_guarda_la_orden_en_una_transaccion(): void
    {
        $repo = $this->createMock(OrdenProduccionRepositoryInterface::class);
        $transactionAggregate = $this->createMock(TransactionAggregate::class);
        $fecha = new DateTimeImmutable('2025-01-01');
        $command = new GenerarOP(null, $fecha, 1, [['sku' => 'ABC', 'qty' => 3]]);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (OrdenProduccion $op) use ($fecha) {
                $this->assertEquals(1, $op->sucursalId());
                $this->assertEquals($fecha, $op->fecha());
                $this->assertCount(1, $op->items());
                return true;
            }))->willReturn(10);
        $transactionAggregate->expects($this->once())->method('runTransaction')
            ->with($this->isInstanceOf(\Closure::class))
            ->willReturnCallback(function (callable $callback) {
                return $callback();
            });
        $handler = new GenerarOPHandler($repo, $transactionAggregate);
        $result = $handler($command);
        $this->assertSame(10, $result);
    }
}