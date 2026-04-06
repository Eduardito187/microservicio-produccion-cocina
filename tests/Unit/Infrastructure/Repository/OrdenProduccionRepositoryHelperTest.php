<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Repository;

use App\Application\Shared\DomainEventPublisherInterface;
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Infrastructure\Persistence\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use DateTimeImmutable;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class OrdenProduccionRepositoryHelperTest
 */
class OrdenProduccionRepositoryHelperTest extends TestCase
{
    private function makeRepository(): OrdenProduccionRepository
    {
        return new OrdenProduccionRepository(
            $this->createMock(OrdenItemRepository::class),
            $this->createMock(ItemDespachoRepository::class),
            $this->createMock(ProduccionBatchRepository::class),
            $this->createMock(DomainEventPublisherInterface::class)
        );
    }

    public function test_private_helpers_cover_date_package_geo_and_address_transformations(): void
    {
        $repo = $this->makeRepository();

        $convertedFromString = $this->invokePrivate($repo, 'convertDate', ['2026-04-06']);
        $convertedFromDate = $this->invokePrivate($repo, 'convertDate', [new \DateTime('2026-04-06 12:00:00')]);

        $this->assertInstanceOf(DateTimeImmutable::class, $convertedFromString);
        $this->assertInstanceOf(DateTimeImmutable::class, $convertedFromDate);

        $package = $this->invokePrivate($repo, 'buildPackageNumber', ['550e8400-e29b-41d4-a716-446655440000']);
        $this->assertStringStartsWith('PKG-', $package);

        $direccion = new \App\Infrastructure\Persistence\Model\Direccion;
        $direccion->linea1 = 'Street 1';
        $direccion->linea2 = '';
        $direccion->ciudad = 'Bogota';
        $direccion->provincia = null;
        $direccion->pais = 'CO';

        $address = $this->invokePrivate($repo, 'buildDeliveryAddress', [$direccion]);
        $this->assertSame('Street 1, Bogota, CO', $address);

        $this->assertSame(1.23, $this->invokePrivate($repo, 'extractLatitude', [['lat' => '1.23']]));
        $this->assertSame(4.56, $this->invokePrivate($repo, 'extractLongitude', [['lng' => 4.56]]));
        $this->assertSame(0.0, $this->invokePrivate($repo, 'extractLatitude', [['lat' => 'bad']]));
        $this->assertSame(0.0, $this->invokePrivate($repo, 'extractLongitude', [['x' => 1]]));
    }

    public function test_resolve_entrega_and_contrato_return_null_for_invalid_inputs(): void
    {
        $repo = $this->makeRepository();

        $this->assertNull($this->invokePrivate($repo, 'resolveEntregaIdFromVentana', [null]));
        $this->assertNull($this->invokePrivate($repo, 'resolveEntregaIdFromVentana', [123]));
        $this->assertNull($this->invokePrivate($repo, 'resolveEntregaIdFromVentana', ['']));

        $this->assertNull($this->invokePrivate($repo, 'resolveContratoIdFromVentana', [null]));
        $this->assertNull($this->invokePrivate($repo, 'resolveContratoIdFromVentana', [123]));
        $this->assertNull($this->invokePrivate($repo, 'resolveContratoIdFromVentana', ['']));
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }
}
