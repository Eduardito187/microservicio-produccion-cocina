<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Shared\DomainEventPublisherInterface;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * @class MaestrosHandlersBulkSmokeTest
 */
class MaestrosHandlersBulkSmokeTest extends TestCase
{
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface
        {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @dataProvider handlersProvider
     */
    public function test_handler_se_puede_ejecutar_en_memoria(string $data): void
    {
        $handlerReflectionClass = new ReflectionClass($data);
        $constructor = $handlerReflectionClass->getConstructor();

        $baseName = $handlerReflectionClass->getShortName();
        $entityName = preg_replace('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', '', $baseName);
        $entityName = preg_replace('/Handler$/', '', (string) $entityName);
        $entityName = preg_replace('/s$/', '', (string) $entityName);

        $entityName = match ($entityName) {
            'Direccione' => 'Direccion',
            'CalendarioItem' => 'CalendarioItem',
            'Calendario' => 'Calendario',
            'Producto' => 'Products',
            'RecetasVersione', 'RecetaVersione' => 'RecetaVersion',
            'Suscripcione' => 'Suscripcion',
            'VentanasEntrega' => 'VentanaEntrega',
            default => $entityName,
        };

        $entityReflectionName = 'App\\Domain\\Produccion\\Entity\\' . $entityName;
        $entity = class_exists($entityReflectionName) ? $this->instantiateWithDummies($entityReflectionName) : null;

        $args = [];
        $repository = null;
        if ($constructor) {
            foreach ($constructor->getParameters() as $idx => $param) {
                $type = $param->getType();
                if (! $type instanceof ReflectionNamedType) {
                    $args[] = $param->allowsNull() ? null : 'TEST';
                    continue;
                }

                $typeName = $type->getName();
                if ($typeName === TransactionAggregate::class) {
                    $args[] = $this->tx();
                    continue;
                }
                if ($typeName === DomainEventPublisherInterface::class) {
                    $args[] = $this->createMock(DomainEventPublisherInterface::class);
                    continue;
                }
                if (interface_exists($typeName)) {
                    $mock = $this->createMock($typeName);
                    if ($idx === 0) {
                        $repository = $mock;
                    }
                    $args[] = $mock;
                    continue;
                }

                $args[] = $this->dummyValueForType($typeName, $param->allowsNull());
            }
        }

        if ($repository !== null) {
            if (str_starts_with($baseName, 'Crear')) {
                if (method_exists($repository, 'save')) {
                    $repository->method('save')->willReturn('e28e9cc2-5225-40c0-b88b-2341f96d76a3');
                }
            } elseif (str_starts_with($baseName, 'Actualizar')) {
                if (method_exists($repository, 'byId')) {
                    $repository->method('byId')->willReturn($entity ?? $this->dummyEntityFromRepositoryById($repository));
                }
                if (method_exists($repository, 'save')) {
                    $repository->method('save')->willReturn('e28e9cc2-5225-40c0-b88b-2341f96d76a3');
                }
            } elseif (str_starts_with($baseName, 'Eliminar')) {
                if (method_exists($repository, 'byId')) {
                    $repository->method('byId')->willReturn($entity ?? $this->dummyEntityFromRepositoryById($repository));
                }
            } elseif (str_starts_with($baseName, 'Ver')) {
                if (method_exists($repository, 'byId')) {
                    $repository->method('byId')->willReturn($entity ?? $this->dummyEntityFromRepositoryById($repository));
                }
            } elseif (str_starts_with($baseName, 'Listar')) {
                if (method_exists($repository, 'list')) {
                    $repository->method('list')->willReturn($entity ? [$entity] : []);
                }
            }
        }

        $invoke = $handlerReflectionClass->getMethod('__invoke');
        $cmdType = $invoke->getParameters()[0]->getType();
        $cmdFqcn = ($cmdType instanceof ReflectionNamedType) ? $cmdType->getName() : null;
        $this->assertNotNull($cmdFqcn);
        $command = $this->instantiateWithDummies($cmdFqcn);

        $handler = $handlerReflectionClass->newInstanceArgs($args);
        $result = $handler($command);

        if (str_starts_with($baseName, 'Ver')) {
            $this->assertIsArray($result);
            $this->assertArrayHasKey('id', $result);
        } elseif (str_starts_with($baseName, 'Listar')) {
            $this->assertIsArray($result);
        } else {
            $this->assertTrue(true);
        }
    }

    public static function handlersProvider(): array
    {
        $handlersPath = realpath(__DIR__ . '/../../../../app/Application/Produccion/Handler');
        $dir = is_string($handlersPath) ? $handlersPath . '/*.php' : '';
        $out = [];

        foreach (($dir !== '' ? glob($dir) : []) ?: [] as $file) {
            $class = basename($file, '.php');

            if (! preg_match('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', $class)) {
                continue;
            }
            if (str_contains($class, 'OP')) {
                continue;
            }
            if (str_contains($class, 'DesdeLogistica')) {
                continue;
            }
            if (str_contains($class, 'Orden')) {
                continue;
            }

            $out[$class] = ['App\\Application\\Produccion\\Handler\\' . $class];
        }

        if ($out === []) {
            $fallback = [
                'App\\Application\\Produccion\\Handler\\CrearPacienteHandler',
                'App\\Application\\Produccion\\Handler\\VerPacienteHandler',
                'App\\Application\\Produccion\\Handler\\ListarPacientesHandler',
            ];

            foreach ($fallback as $handler) {
                if (class_exists($handler)) {
                    $out[$handler] = [$handler];
                }
            }
        }

        return $out;
    }

    private function instantiateWithDummies(string $data): object
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
                    $args[] = $param->allowsNull() ? null : 'TEST';
                }
            }
        }

        return $reflectionClass->newInstanceArgs($args);
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
        if (interface_exists($typeName)) {
            return $this->createMock($typeName);
        }

        if (class_exists($typeName)) {
            $reflectionClass = new ReflectionClass($typeName);
            if ($reflectionClass->isInstantiable()) {
                $constructor = $reflectionClass->getConstructor();

                if (! $constructor || $constructor->getNumberOfRequiredParameters() === 0) {
                    return $reflectionClass->newInstance();
                }
            }
        }

        return new class {};
    }

    private function dummyEntityFromRepositoryById(object $repository): mixed
    {
        try {
            $method = new \ReflectionMethod($repository, 'byId');
            $returnType = $method->getReturnType();

            if ($returnType instanceof ReflectionNamedType) {
                $typeName = $returnType->getName();
                if ($returnType->allowsNull()) {
                    return $this->dummyValueForType($typeName, true) ?? $this->dummyObject($typeName);
                }

                return $this->dummyValueForType($typeName, false);
            }
        } catch (\Throwable $e) {
            // Best effort for smoke tests.
        }

        return new class {};
    }
}
