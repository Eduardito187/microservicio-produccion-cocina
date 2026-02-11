<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\Etiqueta;
use App\Domain\Produccion\Aggregate\Paquete;
use PHPUnit\Framework\TestCase;

/**
 * @class SimpleAggregatesTest
 * @package Tests\Unit\Domain\Produccion\Aggregate
 */
class SimpleAggregatesTest extends TestCase
{
    /**
     * @param object $object
     * @param string $prop
     */
    private function getPrivate(object $object, string $prop)
    {
        $reflectionClass = new \ReflectionClass($object);
        $property = $reflectionClass->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @return void
     */
    public function test_etiqueta_crear_sets_fields(): void
    {
        $etiqueta = Etiqueta::crear(null, 5, 7, 9, ['a' => 1]);

        $this->assertNull($this->getPrivate($etiqueta, 'id'));
        $this->assertSame(5, $this->getPrivate($etiqueta, 'recetaVersionId'));
        $this->assertSame(7, $this->getPrivate($etiqueta, 'suscripcionId'));
        $this->assertSame(9, $this->getPrivate($etiqueta, 'pacienteId'));
        $this->assertSame(['a' => 1], $this->getPrivate($etiqueta, 'qrPayload'));
    }

    /**
     * @return void
     */
    public function test_etiqueta_reconstitute_sets_id(): void
    {
        $etiqueta = Etiqueta::reconstitute(11, 5, 7, 9, []);
        $this->assertSame(11, $this->getPrivate($etiqueta, 'id'));
    }

    /**
     * @return void
     */
    public function test_paquete_crear_and_reconstitute(): void
    {
        $paquete1 = Paquete::crear(null, 1, 2, 3);
        $this->assertNull($this->getPrivate($paquete1, 'id'));
        $this->assertSame(1, $this->getPrivate($paquete1, 'etiquetaId'));
        $this->assertSame(2, $this->getPrivate($paquete1, 'ventanaId'));
        $this->assertSame(3, $this->getPrivate($paquete1, 'direccionId'));

        $paquete2 = Paquete::reconstitute(99, 1, 2, 3);
        $this->assertSame(99, $this->getPrivate($paquete2, 'id'));
    }
}
