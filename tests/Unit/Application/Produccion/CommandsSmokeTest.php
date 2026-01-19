<?php

namespace Tests\Unit\Application\Produccion;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Smoke tests para cubrir constructores de Commands.
 *
 * - No toca DB ni framework.
 * - Recorre todos los Commands del folder y los instancia con valores dummy
 *   según tipos del constructor.
 */
class CommandsSmokeTest extends TestCase
{
    /**
     * @dataProvider commandsProvider
     */
    public function test_commands_se_pueden_instanciar(string $fqcn): void
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
                    // fallback: null si no podemos inferir (deberia ser raro aquí)
                    $args[] = null;
                }
            }
        }

        $obj = $rc->newInstanceArgs($args);
        $this->assertInstanceOf($fqcn, $obj);
    }

    public static function commandsProvider(): array
    {
        $root = dirname(__DIR__, 4); // .../tests
        $base = dirname($root);      // project root
        $dir = $base.'/app/Application/Produccion/Command/*.php';

        $out = [];
        foreach (glob($dir) ?: [] as $file) {
            $class = basename($file, '.php');
            $out[$class] = ['App\\Application\\Produccion\\Command\\'.$class];
        }

        // Evita caso borde si el glob no devuelve nada por rutas.
        if ($out === []) {
            $out['CrearCalendario'] = ['App\\Application\\Produccion\\Command\\CrearCalendario'];
        }

        return $out;
    }

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

    private function dummyObject(string $typeName): object
    {
        // Para casos raros donde el command recibe objetos.
        if (class_exists($typeName)) {
            $rc = new ReflectionClass($typeName);
            if ($rc->isInstantiable()) {
                $ctor = $rc->getConstructor();
                if (!$ctor || $ctor->getNumberOfRequiredParameters() === 0) {
                    return $rc->newInstance();
                }
            }
        }

        // fallback seguro
        return new class {
        };
    }
}
