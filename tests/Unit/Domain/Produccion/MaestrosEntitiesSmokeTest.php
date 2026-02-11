<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use ReflectionUnionType;
use DateTimeImmutable;
use ReflectionClass;

/**
 * @class MaestrosEntitiesSmokeTest
 * @package Tests\Unit\Domain\Produccion
 */
class MaestrosEntitiesSmokeTest extends TestCase
{
    /**
     * @dataProvider entitiesProvider
     */
    public function test_entities_se_pueden_instanciar(string $data): void
    {
        $reflectionClass = new ReflectionClass($data);
        $constructor = $reflectionClass->getConstructor();
        $args = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType) {
                    $args[] = $this->dummyValueForType($type->getName(), $param->allowsNull());
                    continue;
                }

                if ($type instanceof ReflectionUnionType) {
                    $args[] = $this->dummyValueForUnion($type, $param->allowsNull());
                    continue;
                }

                $args[] = null;
            }
        }

        $obj = $reflectionClass->newInstanceArgs($args);
        $this->assertInstanceOf($data, $obj);
    }

    /**
     * @return array
     */
    public static function entitiesProvider(): array
    {
        $root = dirname(__DIR__, 3);
        $base = dirname($root);
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

    /**
     * @param string $typeName
     * @param bool $nullable
     * @return mixed
     */
    private function dummyValueForType(string $typeName, bool $nullable): mixed
    {
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

    /**
     * @param string $typeName
     * @return object
     */
    private function dummyObject(string $typeName): object
    {
        if (class_exists($typeName)) {
            $reflectionClass = new ReflectionClass($typeName);

            if ($reflectionClass->isInstantiable()) {
                $constructor = $reflectionClass->getConstructor();

                if (!$constructor || $constructor->getNumberOfRequiredParameters() === 0) {
                    return $reflectionClass->newInstance();
                }
            }
        }

        return new class {};
    }

    /**
     * @param ReflectionUnionType $type
     * @param bool $nullable
     * @return mixed
     */
    private function dummyValueForUnion(ReflectionUnionType $type, bool $nullable): mixed
    {
        $fallback = null;

        foreach ($type->getTypes() as $unionType) {
            if ($unionType instanceof ReflectionNamedType && $unionType->getName() !== 'null') {
                $value = $this->dummyValueForType($unionType->getName(), $nullable);

                if ($value !== null || $nullable) {
                    return $value;
                }

                $fallback = $value;
            }
        }

        if ($nullable) {
            return null;
        }

        return $fallback ?? 'TEST';
    }
}
