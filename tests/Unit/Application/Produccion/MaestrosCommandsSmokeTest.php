<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * @class MaestrosCommandsSmokeTest
 * @package Tests\Unit\Application\Produccion
 */
final class MaestrosCommandsSmokeTest extends TestCase
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
            foreach ($constructor->getParameters() as $p) {
                $type = $p->getType();

                if ($type instanceof ReflectionNamedType) {
                    $args[] = $this->dummyValueForType($type->getName(), $p->allowsNull());
                } elseif ($type instanceof ReflectionUnionType) {
                    $args[] = $this->dummyValueForUnion($type, $p->allowsNull());
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
        $classes = [
            // Actualizar*
            'App\\Application\\Produccion\\Command\\ActualizarCalendarioItem',
            'App\\Application\\Produccion\\Command\\ActualizarEstacion',
            'App\\Application\\Produccion\\Command\\ActualizarEtiqueta',
            'App\\Application\\Produccion\\Command\\ActualizarPaciente',
            'App\\Application\\Produccion\\Command\\ActualizarPaquete',
            'App\\Application\\Produccion\\Command\\ActualizarPorcion',
            'App\\Application\\Produccion\\Command\\ActualizarRecetaVersion',
            'App\\Application\\Produccion\\Command\\ActualizarSuscripcion',
            'App\\Application\\Produccion\\Command\\ActualizarVentanaEntrega',

            // Crear*
            'App\\Application\\Produccion\\Command\\CrearCalendarioItem',
            'App\\Application\\Produccion\\Command\\CrearEtiqueta',
            'App\\Application\\Produccion\\Command\\CrearPaciente',
            'App\\Application\\Produccion\\Command\\CrearPaquete',
            'App\\Application\\Produccion\\Command\\CrearPorcion',
            'App\\Application\\Produccion\\Command\\CrearRecetaVersion',
            'App\\Application\\Produccion\\Command\\CrearSuscripcion',
            'App\\Application\\Produccion\\Command\\CrearVentanaEntrega',

            // Eliminar*
            'App\\Application\\Produccion\\Command\\EliminarCalendarioItem',
            'App\\Application\\Produccion\\Command\\EliminarEstacion',
            'App\\Application\\Produccion\\Command\\EliminarEtiqueta',
            'App\\Application\\Produccion\\Command\\EliminarPaciente',
            'App\\Application\\Produccion\\Command\\EliminarPaquete',
            'App\\Application\\Produccion\\Command\\EliminarPorcion',
            'App\\Application\\Produccion\\Command\\EliminarRecetaVersion',
            'App\\Application\\Produccion\\Command\\EliminarSuscripcion',
            'App\\Application\\Produccion\\Command\\EliminarVentanaEntrega',

            // Ver*
            'App\\Application\\Produccion\\Command\\VerCalendarioItem',
            'App\\Application\\Produccion\\Command\\VerEstacion',
            'App\\Application\\Produccion\\Command\\VerEtiqueta',
            'App\\Application\\Produccion\\Command\\VerPaciente',
            'App\\Application\\Produccion\\Command\\VerPaquete',
            'App\\Application\\Produccion\\Command\\VerPorcion',
            'App\\Application\\Produccion\\Command\\VerRecetaVersion',
            'App\\Application\\Produccion\\Command\\VerSuscripcion',
            'App\\Application\\Produccion\\Command\\VerVentanaEntrega',
        ];

        $out = [];

        foreach ($classes as $data) {
            $out[$data] = [$data];
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
            'array' => ['x' => 1],
            'bool' => true,
            DateTimeImmutable::class, 'DateTimeImmutable' => new DateTimeImmutable('2026-01-10 10:00:00'),
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
