<?php

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Smoke tests para cubrir constructores de Entities "maestras".
 *
 * Suben cobertura de App\Domain\Produccion\Entity\*.
 */
class MaestrosEntitiesSmokeTest extends TestCase
{
    /**
     * @dataProvider entitiesProvider
     */
    public function test_entities_se_pueden_instanciar(string $fqcn): void
    {
        $rc = new ReflectionClass($fqcn);
        $ctor = $rc->getConstructor();

        $args = [];
        if ($ctor) {
            foreach ($ctor->getParameters() as $p) {
                $type = $p->getType();
                if ($type instanceof ReflectionNamedType) {
                    $args[] = $this->dummyValueForType($type->getName(), $p->allowsNull());
                } else {
                    $args[] = null;
                }
            }
        }

        $obj = $rc->newInstanceArgs($args);
        $this->assertInstanceOf($fqcn, $obj);
    }

    public static function entitiesProvider(): array
    {
        $root = dirname(__DIR__, 3); // .../tests
        $base = dirname($root);      // project root
        $dir = $base.'/app/Domain/Produccion/Entity/*.php';

        $out = [];
        foreach (glob($dir) ?: [] as $file) {
            $class = basename($file, '.php');
            $out[$class] = ['App\\Domain\\Produccion\\Entity\\'.$class];
        }

        if ($out === []) {
            $out['Calendario'] = ['App\\Domain\\Produccion\\Entity\\Calendario'];
        }

        return $out;
    }

    private function dummyValueForType(string $typeName, bool $nullable): mixed
    {
        // Value Objects explícitos (evita class@anonymous en entidades como OrdenItem)
        if ($typeName === Qty::class) {
            return new Qty(1);
        }

        if ($typeName === Sku::class) {
            return new Sku('SKU-TEST');
        }

        return match ($typeName) {
            'int' => 1,
            'float' => 10.5,
            'string' => 'TEST',
            'array' => [],
            'bool' => true,
            DateTimeImmutable::class, 'DateTimeImmutable' => new DateTimeImmutable('2026-01-10'),
            default => $nullable ? null : $this->dummyObject($typeName),
        };
    }

    private function dummyObject(string $typeName): object
    {
        if (class_exists($typeName)) {
            $rc = new ReflectionClass($typeName);
            if ($rc->isInstantiable()) {
                $ctor = $rc->getConstructor();
                if (!$ctor || $ctor->getNumberOfRequiredParameters() === 0) {
                    return $rc->newInstance();
                }
            }
        }

        return new class {
        };
    }
}