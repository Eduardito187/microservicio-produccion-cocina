<?php

namespace Tests\Unit\Domain\Produccion\Aggregate;

use App\Domain\Produccion\Aggregate\Etiqueta;
use App\Domain\Produccion\Aggregate\Paquete;
use PHPUnit\Framework\TestCase;

class SimpleAggregatesTest extends TestCase
{
    /**
     * @inheritDoc
     */
    private function getPrivate(object $obj, string $prop)
    {
        $r = new \ReflectionClass($obj);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($obj);
    }

    /**
     * @inheritDoc
     */
    public function test_etiqueta_crear_sets_fields(): void
    {
        $e = Etiqueta::crear(null, recetaVersionId: 5, suscripcionId: 7, pacienteId: 9, qrPayload: ['a' => 1]);

        $this->assertNull($this->getPrivate($e, 'id'));
        $this->assertSame(5, $this->getPrivate($e, 'recetaVersionId'));
        $this->assertSame(7, $this->getPrivate($e, 'suscripcionId'));
        $this->assertSame(9, $this->getPrivate($e, 'pacienteId'));
        $this->assertSame(['a' => 1], $this->getPrivate($e, 'qrPayload'));
    }

    /**
     * @inheritDoc
     */
    public function test_etiqueta_reconstitute_sets_id(): void
    {
        $e = Etiqueta::reconstitute(11, recetaVersionId: 5, suscripcionId: 7, pacienteId: 9, qrPayload: []);
        $this->assertSame(11, $this->getPrivate($e, 'id'));
    }

    /**
     * @inheritDoc
     */
    public function test_paquete_crear_and_reconstitute(): void
    {
        $p1 = Paquete::crear(null, etiquetaId: 1, ventanaId: 2, direccionId: 3);
        $this->assertNull($this->getPrivate($p1, 'id'));
        $this->assertSame(1, $this->getPrivate($p1, 'etiquetaId'));
        $this->assertSame(2, $this->getPrivate($p1, 'ventanaId'));
        $this->assertSame(3, $this->getPrivate($p1, 'direccionId'));

        $p2 = Paquete::reconstitute(99, etiquetaId: 1, ventanaId: 2, direccionId: 3);
        $this->assertSame(99, $this->getPrivate($p2, 'id'));
    }
}