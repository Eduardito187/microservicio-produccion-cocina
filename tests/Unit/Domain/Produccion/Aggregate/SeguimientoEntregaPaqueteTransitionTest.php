<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\SeguimientoEntregaPaquete;
use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;
use PHPUnit\Framework\TestCase;

/**
 * @class SeguimientoEntregaPaqueteTransitionTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class SeguimientoEntregaPaqueteTransitionTest extends TestCase
{
    /**
     * @return void
     */
    public function test_in_transit_a_completed_es_valido(): void
    {
        $aggregate = new SeguimientoEntregaPaquete(
            '56922457-0240-49c7-a45b-eed4bb863332',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            new PackageStatus('en_ruta'),
            false,
            null,
            null
        );

        $changed = $aggregate->applyStatus(
            new PackageStatus('confirmada'),
            new DriverId('9ddf07e9-be4c-45cc-924a-3b64d84f567b'),
            new OccurredOn('2026-03-02T10:00:00Z')
        );

        $this->assertTrue($changed);
        $this->assertTrue($aggregate->isCompleted());
    }

    /**
     * @return void
     */
    public function test_completed_a_otro_estado_esta_bloqueado(): void
    {
        $aggregate = new SeguimientoEntregaPaquete(
            '56922457-0240-49c7-a45b-eed4bb863332',
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            new PackageStatus('confirmada'),
            true,
            new DriverId('9ddf07e9-be4c-45cc-924a-3b64d84f567b'),
            new OccurredOn('2026-03-02T10:00:00Z')
        );

        $changed = $aggregate->applyStatus(
            new PackageStatus('fallida'),
            new DriverId('9ddf07e9-be4c-45cc-924a-3b64d84f567b'),
            new OccurredOn('2026-03-02T10:05:00Z')
        );

        $this->assertFalse($changed);
        $this->assertTrue($aggregate->isCompleted());
    }
}
