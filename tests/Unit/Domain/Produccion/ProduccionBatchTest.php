<?php

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use PHPUnit\Framework\TestCase;
use DomainException;

class ProduccionBatchTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_procesar_y_despachar_cambian_estado_y_cantidades(): void
    {
        $batch = new ProduccionBatch(
            id: 1,
            ordenProduccionId: 10,
            productoId: 99,
            estacionId: 1,
            recetaVersionId: 1,
            porcionId: 1,
            cantPlanificada: 5,
            cantProducida: 0,
            mermaGr: 0,
            estado: EstadoPlanificado::PROGRAMADO,
            rendimiento: 0,
            qty: new Qty(5),
            posicion: 1,
            ruta: []
        );

        $batch->procesar();
        $this->assertSame(EstadoPlanificado::PROCESANDO, $batch->estado);
        $this->assertSame(5, $batch->cantProducida);

        $batch->despachar();
        $this->assertSame(EstadoPlanificado::DESPACHADO, $batch->estado);
    }

    /**
     * @inheritDoc
     */
    public function test_no_permite_despachar_si_no_esta_procesando(): void
    {
        $batch = new ProduccionBatch(
            id: 1,
            ordenProduccionId: 10,
            productoId: 99,
            estacionId: 1,
            recetaVersionId: 1,
            porcionId: 1,
            cantPlanificada: 5,
            cantProducida: 0,
            mermaGr: 0,
            estado: EstadoPlanificado::PROGRAMADO,
            rendimiento: 0,
            qty: new Qty(5),
            posicion: 1,
            ruta: []
        );

        $this->expectException(DomainException::class);
        $batch->despachar();
    }
}