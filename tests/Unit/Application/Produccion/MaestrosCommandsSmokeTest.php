<?php

namespace Tests\Unit\Application\Produccion;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

final class MaestrosCommandsSmokeTest extends TestCase
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
                    $args[] = null;
                }
            }
        }

        $obj = $rc->newInstanceArgs($args);
        $this->assertInstanceOf($fqcn, $obj);
    }

    public static function commandsProvider(): array
    {
        // Solo los que listaste con 0%
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
        foreach ($classes as $fqcn) {
            $out[$fqcn] = [$fqcn];
        }
        return $out;
    }

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