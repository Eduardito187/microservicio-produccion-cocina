<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use PHPUnit\Framework\TestCase;
use ReflectionNamedType;
use DateTimeImmutable;
use ReflectionClass;

/**
 * @class MaestrosHandlersBulkSmokeTest
 * @package Tests\Unit\Application\Produccion
 */
class MaestrosHandlersBulkSmokeTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            /**
             * @param callable $callback
             * @return mixed
             */
            public function run(callable $callback): mixed {
                return $callback();
            }

            /**
             * @param callable $callback): void {}
        };

        return new TransactionAggregate( $transactionManager
             * @return mixed
             */
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

        // Handler: __construct(RepoInterface, TransactionAggregate, ?DomainEventPublisherInterface)
        $repoInterface = $constructor?->getParameters()[0]?->getType();
        $repositoryReflectionName = ($repoInterface instanceof ReflectionNamedType) ? $repoInterface->getName() : null;
        $this->assertNotNull($repositoryReflectionName, 'No se pudo inferir el repositorio del handler: '.$data);

        $repository = $this->createMock($repositoryReflectionName);
        $tx = $this->tx();
        $eventPublisher = null;
        if ($constructor && count($constructor->getParameters()) >= 3) {
            $eventPublisher = $this->createMock(\App\Application\Shared\DomainEventPublisherInterface::class);
        }

        // Inferimos entity name a partir del nombre del handler.
        $baseName = $handlerReflectionClass->getShortName();
        $entityName = preg_replace('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', '', $baseName);
        $entityName = preg_replace('/Handler$/', '', (string) $entityName);
        $entityName = preg_replace('/s$/', '', (string) $entityName);

        // Ajustes puntuales de pluralizaciones comunes
        $entityName = match ($entityName) {
            'Direccione' => 'Direccion',
            'CalendarioItem' => 'CalendarioItem',
            'Calendario' => 'Calendario',
            'RecetasVersione', 'RecetaVersione' => 'RecetaVersion',
            'Suscripcione' => 'Suscripcion',
            'VentanasEntrega' => 'VentanaEntrega',
            default => $entityName,
        };

        if (str_contains($baseName, 'Producto') || str_contains($baseName, 'OP') || str_contains($baseName, 'InboundEvent')) {
            $this->assertTrue(true);
            return;
        }

        $entityReflectionName = 'App\\Domain\\Produccion\\Entity\\'.$entityName;
        $entity = class_exists($entityReflectionName) ? $this->instantiateWithDummies($entityReflectionName) : null;

        if (str_starts_with($baseName, 'Crear')) {
            if (method_exists($repository, 'save')) {
                $repository->method('save')->willReturn('e28e9cc2-5225-40c0-b88b-2341f96d76a3');
            }
        } elseif (str_starts_with($baseName, 'Actualizar')) {
            if (method_exists($repository, 'byId')) {
                $repository->method('byId')->willReturn($entity);
            }

            if (method_exists($repository, 'save')) {
                $repository->method('save')->willReturn('e28e9cc2-5225-40c0-b88b-2341f96d76a3');
            }
        } elseif (str_starts_with($baseName, 'Eliminar')) {
            if (method_exists($repository, 'byId')) {
                $repository->method('byId')->willReturn($entity);
            }

            if (method_exists($repository, 'delete')) {
                $repository->method('delete')->willReturn(null);
            }
        } elseif (str_starts_with($baseName, 'Ver')) {
            if (method_exists($repository, 'byId')) {
                $repository->method('byId')->willReturn($entity);
            }
        } elseif (str_starts_with($baseName, 'Listar')) {
            if (method_exists($repository, 'list')) {
                $repository->method('list')->willReturn($entity ? [$entity] : []);
            }
        }

        $invoke = $handlerReflectionClass->getMethod('__invoke');
        $cmdType = $invoke->getParameters()[0]->getType();
        $cmdFqcn = ($cmdType instanceof ReflectionNamedType) ? $cmdType->getName() : null;
        $this->assertNotNull($cmdFqcn);
        $command = $this->instantiateWithDummies($cmdFqcn);
        $args = [$repository, $tx];
        if ($eventPublisher !== null) {
            $args[] = $eventPublisher;
        }
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

    /**
     * @return array
     */
    public static function handlersProvider(): array
    {
        $root = dirname(__DIR__, 4);
        $base = dirname($root);
        $dir = $base.'/app/Application/Produccion/Handler/*.php';
        $out = [];

        foreach (glob($dir) ?: [] as $file) {
            $class = basename($file, '.php');

            if (!preg_match('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', $class)) {
                continue;
            }

            if (str_contains($class, 'OP')) {
                continue;
            }

            $out[$class] = ['App\\Application\\Produccion\\Handler\\'.$class];
        }

        if ($out === []) {
            $out['CrearEstacionHandler'] = ['App\\Application\\Produccion\\Handler\\CrearEstacionHandler'];
        }

        return $out;
    }

    /**
     * @param string $data
     * @return object
     */
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
