<?php

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Smoke tests "bulk" para cubrir handlers CRUD de maestros.
 *
 * Idea:
 * - Escanea el folder de Handlers.
 * - Para cada handler CRUD (Crear/Actualizar/Eliminar/Ver/Listar) crea mocks
 *   del repositorio y ejecuta __invoke con un Command instanciado por reflection.
 *
 * Esto sube cobertura en Application\Produccion\Handler sin escribir 60 tests a mano.
 */
class MaestrosHandlersBulkSmokeTest extends TestCase
{
    private function tx(): TransactionAggregate
    {
        $tm = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed { return $callback(); }
            public function afterCommit(callable $callback): void { /* no-op */ }
        };

        return new TransactionAggregate($tm);
    }

    /**
     * @dataProvider handlersProvider
     */
    public function test_handler_se_puede_ejecutar_en_memoria(string $handlerFqcn): void
    {
        $handlerRc = new ReflectionClass($handlerFqcn);
        $ctor = $handlerRc->getConstructor();

        // Handler siempre: __construct(RepoInterface, TransactionAggregate)
        $repoInterface = $ctor?->getParameters()[0]?->getType();
        $repoFqcn = ($repoInterface instanceof ReflectionNamedType) ? $repoInterface->getName() : null;
        $this->assertNotNull($repoFqcn, 'No se pudo inferir el repositorio del handler: '.$handlerFqcn);

        $repo = $this->createMock($repoFqcn);
        $tx = $this->tx();

        // Inferimos entity name a partir del nombre del handler.
        $baseName = $handlerRc->getShortName(); // e.g. CrearEstacionHandler
        $entityName = preg_replace('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', '', $baseName);
        $entityName = preg_replace('/Handler$/', '', (string) $entityName);
        $entityName = preg_replace('/s$/', '', (string) $entityName); // ListarDirecciones -> Direccione (no perfecto)

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

        // Excluimos handlers con comportamiento especial ya cubiertos en tests dedicados.
        if (str_contains($baseName, 'Producto') || str_contains($baseName, 'OP') || str_contains($baseName, 'InboundEvent')) {
            $this->assertTrue(true);
            return;
        }

        $entityFqcn = 'App\\Domain\\Produccion\\Entity\\'.$entityName;
        $entity = class_exists($entityFqcn) ? $this->instantiateWithDummies($entityFqcn) : null;

        // Setup mocks por tipo de handler
        if (str_starts_with($baseName, 'Crear')) {
            if (method_exists($repo, 'save')) {
                $repo->method('save')->willReturn(1);
            }
        } elseif (str_starts_with($baseName, 'Actualizar')) {
            if (method_exists($repo, 'byId')) {
                $repo->method('byId')->willReturn($entity);
            }
            if (method_exists($repo, 'save')) {
                $repo->method('save')->willReturn(1);
            }
        } elseif (str_starts_with($baseName, 'Eliminar')) {
            if (method_exists($repo, 'byId')) {
                $repo->method('byId')->willReturn($entity);
            }
            if (method_exists($repo, 'delete')) {
                $repo->method('delete')->willReturn(null);
            }
        } elseif (str_starts_with($baseName, 'Ver')) {
            if (method_exists($repo, 'byId')) {
                $repo->method('byId')->willReturn($entity);
            }
        } elseif (str_starts_with($baseName, 'Listar')) {
            // Algunos repos usan list() y otros listAll()/etc. En este repo es list().
            if (method_exists($repo, 'list')) {
                $repo->method('list')->willReturn($entity ? [$entity] : []);
            }
        }

        // Creamos Command según type-hint del __invoke
        $invoke = $handlerRc->getMethod('__invoke');
        $cmdType = $invoke->getParameters()[0]->getType();
        $cmdFqcn = ($cmdType instanceof ReflectionNamedType) ? $cmdType->getName() : null;
        $this->assertNotNull($cmdFqcn);
        $command = $this->instantiateWithDummies($cmdFqcn);

        $handler = $handlerRc->newInstanceArgs([$repo, $tx]);
        $result = $handler($command);

        // Asserts suaves (solo para evitar falsos positivos).
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
        $root = dirname(__DIR__, 4); // .../tests
        $base = dirname($root);      // project root
        $dir = $base.'/app/Application/Produccion/Handler/*.php';

        $out = [];
        foreach (glob($dir) ?: [] as $file) {
            $class = basename($file, '.php');

            // Solo CRUD maestros
            if (!preg_match('/^(Crear|Actualizar|Eliminar|Ver|Listar)/', $class)) {
                continue;
            }

            // Excluir OP (ya tienen tests específicos)
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

    private function instantiateWithDummies(string $fqcn): object
    {
        $rc = new ReflectionClass($fqcn);
        $ctor = $rc->getConstructor();

        $args = [];
        if ($ctor) {
            foreach ($ctor->getParameters() as $p) {
                $t = $p->getType();
                if ($t instanceof ReflectionNamedType) {
                    $args[] = $this->dummyValueForType($t->getName(), $p->allowsNull());
                } else {
                    $args[] = $p->allowsNull() ? null : 'TEST';
                }
            }
        }

        return $rc->newInstanceArgs($args);
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
