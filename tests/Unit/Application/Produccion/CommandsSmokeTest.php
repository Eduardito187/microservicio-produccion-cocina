<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use DateTimeImmutable;
use ReflectionClass;

/**
 * @class CommandsSmokeTest
 * @package Tests\Unit\Application\Produccion
 */
class CommandsSmokeTest extends TestCase
{
    /**
     * @dataProvider commandsProvider
     */
    public function test_commands_se_pueden_instanciar(string $data): void
    {
        $reflectionClass = new ReflectionClass($data);
        $constructor = $reflectionClass->getConstructor();
        $args = [];

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType) {
                    $args[] = $this->dummyValueForType($type->getName(), $param->allowsNull());
                } else {
                    $args[] = null;
                }
            }
        }

        $obj = $reflectionClass->newInstanceArgs($args);
        $this->assertInstanceOf($data, $obj);
    }

    /**
     * @return array
     */
    public static function commandsProvider(): array
    {
        $root = dirname(__DIR__, 4);
        $base = dirname($root);
        $dir = $base.'/app/Application/Produccion/Command/*.php';
        $out = [];

        foreach (glob($dir) ?: [] as $file) {
            $class = basename($file, '.php');
            $out[$class] = ['App\\Application\\Produccion\\Command\\'.$class];
        }

        if ($out === []) {
            $out['CrearCalendario'] = ['App\\Application\\Produccion\\Command\\CrearCalendario'];
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
}
