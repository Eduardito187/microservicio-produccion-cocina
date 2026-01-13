<?php

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\OrdenProduccion;
use App\Domain\Produccion\Enum\EstadoOP;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DomainException;

class OrdenProduccionInvalidTransitionsExtraTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function test_no_permite_planificar_si_no_esta_creada(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 1,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::PLANIFICADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $this->expectException(DomainException::class);
        $op->planificar();
    }

    /**
     * @inheritDoc
     */
    public function test_no_permite_procesar_si_no_esta_planificada(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 1,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::CREADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $this->expectException(DomainException::class);
        $op->procesar();
    }

    /**
     * @inheritDoc
     */
    public function test_no_permite_cerrar_si_no_esta_en_proceso(): void
    {
        $op = OrdenProduccion::reconstitute(
            id: 1,
            fecha: new DateTimeImmutable('2025-11-04'),
            sucursalId: 'SCZ-001',
            estado: EstadoOP::PLANIFICADA,
            items: [],
            batches: [],
            itemsDespacho: []
        );

        $this->expectException(DomainException::class);
        $op->cerrar();
    }
}