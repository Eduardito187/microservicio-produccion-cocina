<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion;

use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Produccion\Handler\ActualizarCalendarioHandler;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Application\Produccion\Handler\ActualizarDireccionHandler;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Application\Produccion\Handler\EliminarCalendarioHandler;
use App\Application\Produccion\Handler\EliminarDireccionHandler;
use App\Application\Produccion\Handler\ListarCalendariosHandler;
use App\Application\Produccion\Handler\ListarDireccionesHandler;
use App\Application\Produccion\Handler\CrearCalendarioHandler;
use App\Application\Produccion\Handler\CrearDireccionHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Application\Produccion\Handler\VerCalendarioHandler;
use App\Application\Produccion\Handler\VerDireccionHandler;
use App\Application\Produccion\Command\ActualizarCalendario;
use App\Application\Produccion\Command\ActualizarDireccion;
use App\Application\Produccion\Command\EliminarCalendario;
use App\Application\Produccion\Command\EliminarDireccion;
use App\Application\Produccion\Command\ListarCalendarios;
use App\Application\Produccion\Command\ListarDirecciones;
use App\Application\Produccion\Command\CrearCalendario;
use App\Application\Produccion\Command\CrearDireccion;
use App\Application\Produccion\Command\VerCalendario;
use App\Application\Produccion\Command\VerDireccion;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Entity\Direccion;
use App\Application\Shared\DomainEventPublisherInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

/**
 * @class MaestrosHandlersCrudTest
 * @package Tests\Unit\Application\Produccion
 */
class MaestrosHandlersCrudTest extends TestCase
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
     * @return DomainEventPublisherInterface
     */
    private function eventPublisher(): DomainEventPublisherInterface
    {
        return $this->createMock(DomainEventPublisherInterface::class);
    }

    /**
     * @param class-string $handlerClass
     */
    private function makeHandler(string $handlerClass, object $repository, TransactionAggregate $tx, bool $needsEventPublisher): object
    {
        if ($needsEventPublisher) {
            return new $handlerClass($repository, $tx, $this->eventPublisher());
        }

        return new $handlerClass($repository, $tx);
    }

    /**
     * @return void
     */
    public function test_calendario_crud_handlers_invocan_repositorio_y_mapean_respuesta(): void
    {
        $id10 = '11111111-1111-1111-1111-111111111111';
        $repository = $this->createMock(CalendarioRepositoryInterface::class);
        // Crear
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Calendario $calendario): bool {
                return $calendario->id === null && $calendario->fecha->format('Y-m-d') === '2026-01-10' && $calendario->sucursalId === 'SCZ-001';
            }))->willReturn($id10);
        $crear = new CrearCalendarioHandler($repository, $this->tx(), $this->eventPublisher());
        $id = $crear(new CrearCalendario(new DateTimeImmutable('2026-01-10'), 'SCZ-001'));
        $this->assertSame($id10, $id);
        // Actualizar
        $existing = new Calendario($id10, new DateTimeImmutable('2026-01-10'), 'SCZ-001');
        $repository2 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository2->method('byId')->with($id10)->willReturn($existing);
        $repository2->expects($this->once())->method('save')->willReturn($id10);
        $actualizar = new ActualizarCalendarioHandler($repository2, $this->tx(), $this->eventPublisher());
        $actualizadoId = $actualizar(new ActualizarCalendario($id10, new DateTimeImmutable('2026-01-11'), 'SCZ-002'));
        $this->assertSame($id10, $actualizadoId);
        $this->assertSame('2026-01-11', $existing->fecha->format('Y-m-d'));
        $this->assertSame('SCZ-002', $existing->sucursalId);
        // Ver
        $repository3 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository3->method('byId')->with($id10)->willReturn($existing);
        $ver = new VerCalendarioHandler($repository3, $this->tx());
        $data = $ver(new VerCalendario($id10));
        $this->assertSame(['id' => $id10, 'fecha' => '2026-01-11', 'sucursal_id' => 'SCZ-002'], $data);
        // Listar
        $repository4 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository4->method('list')->willReturn([$existing]);
        $listar = new ListarCalendariosHandler($repository4, $this->tx());
        $list = $listar(new ListarCalendarios());
        $this->assertCount(1, $list);
        $this->assertSame($id10, $list[0]['id']);
        // Eliminar
        $repository5 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository5->method('byId')->with($id10)->willReturn($existing);
        $repository5->expects($this->once())->method('delete')->with($id10);
        $eliminar = new EliminarCalendarioHandler($repository5, $this->tx());
        $eliminar(new EliminarCalendario($id10));
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function test_direccion_crud_handlers_invocan_repositorio_y_mapean_respuesta(): void
    {
        $id20 = '22222222-2222-2222-2222-222222222222';
        $repository = $this->createMock(DireccionRepositoryInterface::class);
        // Crear
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Direccion $direccion): bool {
                return $direccion->id === null && $direccion->nombre === 'Casa' && $direccion->linea1 === 'Av. Siempre Viva 123'
                    && $direccion->ciudad === 'SCZ' && $direccion->geo === ['lat' => -17.78, 'lng' => -63.18];
            }))->willReturn($id20);
        $crear = new CrearDireccionHandler($repository, $this->tx(), $this->eventPublisher());
        $id = $crear(new CrearDireccion(
            'Casa','Av. Siempre Viva 123',null,'SCZ',null,'BO',['lat' => -17.78, 'lng' => -63.18]
        ));
        $this->assertSame($id20, $id);
        // Actualizar
        $existing = new Direccion($id20, 'Casa', 'Av. Siempre Viva 123', null, 'SCZ', null, 'BO', ['lat' => -17.78, 'lng' => -63.18]);
        $repository2 = $this->createMock(DireccionRepositoryInterface::class);
        $repository2->method('byId')->with($id20)->willReturn($existing);
        $repository2->expects($this->once())->method('save')->willReturn($id20);
        $actualizar = new ActualizarDireccionHandler($repository2, $this->tx(), $this->eventPublisher());
        $actualizadoId = $actualizar(new ActualizarDireccion(
            $id20, 'Oficina', 'Calle 1', 'Piso 2', 'LPZ', 'Murillo', 'BO', null
        ));
        $this->assertSame($id20, $actualizadoId);
        $this->assertSame('Oficina', $existing->nombre);
        $this->assertSame('LPZ', $existing->ciudad);
        // Ver
        $repository3 = $this->createMock(DireccionRepositoryInterface::class);
        $repository3->method('byId')->with($id20)->willReturn($existing);
        $ver = new VerDireccionHandler($repository3, $this->tx());
        $data = $ver(new VerDireccion($id20));
        $this->assertSame($id20, $data['id']);
        $this->assertSame('Calle 1', $data['linea1']);
        // Listar
        $repository4 = $this->createMock(DireccionRepositoryInterface::class);
        $repository4->method('list')->willReturn([$existing]);
        $listar = new ListarDireccionesHandler($repository4, $this->tx());
        $list = $listar(new ListarDirecciones());
        $this->assertCount(1, $list);
        $this->assertSame('Oficina', $list[0]['nombre']);
        // Eliminar
        $repository5 = $this->createMock(DireccionRepositoryInterface::class);
        $repository5->method('byId')->with($id20)->willReturn($existing);
        $repository5->expects($this->once())->method('delete')->with($id20);
        $eliminar = new EliminarDireccionHandler($repository5, $this->tx());
        $eliminar(new EliminarDireccion($id20));
        $this->assertTrue(true);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_crear_handler_llama_save_y_devuelve_id(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('save')
            ->with($this->isInstanceOf($data['entity']))->willReturn('33333333-3333-3333-3333-333333333333');
        $handler = $this->makeHandler($data['handlers']['crear'], $repository, $this->tx(), $data['needsEventPublisher'] ?? false);
        $id = $handler($data['commands']['crear']());

        $this->assertSame('33333333-3333-3333-3333-333333333333', $id);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_actualizar_handler_hace_byId_modifica_y_save(array $data): void
    {
        $id10 = '11111111-1111-1111-1111-111111111111';
        $repository = $this->createMock($data['repo']);
        $entity = $data['makeEntity']();
        $repository->expects($this->once())->method('byId')->with($id10)->willReturn($entity);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function ($saved) use ($data): bool {
                if (!($saved instanceof $data['entity'])) return false;

                foreach ($data['expectedAfterUpdate']() as $prop => $expected) {
                    if (!property_exists($saved, $prop)) return false;
                    if ($saved->$prop !== $expected) return false;
                }

                return true;
            }))->willReturn($id10);
        $handler = $this->makeHandler($data['handlers']['actualizar'], $repository, $this->tx(), $data['needsEventPublisher'] ?? false);
        $id = $handler($data['commands']['actualizar']());

        $this->assertSame($id10, $id);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_eliminar_handler_hace_byId_y_delete(array $data): void
    {
        $id10 = '11111111-1111-1111-1111-111111111111';
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('byId')->with($id10)->willReturn($data['makeEntity']());
        $repository->expects($this->once())->method('delete')->with($id10);
        $handler = $this->makeHandler($data['handlers']['eliminar'], $repository, $this->tx(), false);
        $handler($data['commands']['eliminar']());

        $this->assertTrue(true);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_ver_handler_mapea_correcto(array $data): void
    {
        $id10 = '11111111-1111-1111-1111-111111111111';
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('byId')->with($id10)->willReturn($data['makeEntity']());
        $handler = $this->makeHandler($data['handlers']['ver'], $repository, $this->tx(), false);
        $out = $handler($data['commands']['ver']());

        $this->assertSame($data['expectedView'](), $out);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_listar_handler_mapea_lista_correcto(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('list')->willReturn([$data['makeEntity']()]);
        $handler = $this->makeHandler($data['handlers']['listar'], $repository, $this->tx(), false);
        $out = $handler($data['commands']['listar']());

        $this->assertSame([$data['expectedView']()], $out);
    }

    /**
     * @return array
     */
    public static function maestrosProvider(): array
    {
        $dtDesde = new DateTimeImmutable('2026-01-10 10:00:00');
        $dtHasta = new DateTimeImmutable('2026-01-10 12:00:00');
        $id10 = '11111111-1111-1111-1111-111111111111';
        $id20 = '22222222-2222-2222-2222-222222222222';
        $id123 = '33333333-3333-3333-3333-333333333333';
        $idCal = '44444444-4444-4444-4444-444444444444';
        $idItem = '55555555-5555-5555-5555-555555555555';
        $idReceta = '66666666-6666-6666-6666-666666666666';
        $idSuscripcion = '77777777-7777-7777-7777-777777777777';
        $idPaciente = '88888888-8888-8888-8888-888888888888';
        $idEtiqueta = '99999999-9999-9999-9999-999999999999';
        $idVentana = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $idDireccion = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';

        return [
            'CalendarioItem' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\CalendarioItem::class,
                    'repo' => \App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface::class,
                    'needsEventPublisher' => true,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearCalendarioItemHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarCalendarioItemHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarCalendarioItemHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerCalendarioItemHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarCalendarioItemsHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearCalendarioItem($idCal, $idItem),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarCalendarioItem($id10, $idCal, $idItem),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarCalendarioItem($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerCalendarioItem($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarCalendarioItems()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\CalendarioItem($id10, $idCal, $idItem),
                    'expectedAfterUpdate' => fn() => ['calendarioId' => $idCal, 'itemDespachoId' => $idItem],
                    'expectedView' => fn() => ['id' => $id10, 'calendario_id' => $idCal, 'item_despacho_id' => $idItem]
                ]
            ],
            'Estacion' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Estacion::class,
                    'repo' => \App\Domain\Produccion\Repository\EstacionRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearEstacionHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarEstacionHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarEstacionHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerEstacionHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarEstacionesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearEstacion('Estacion 1', 5),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarEstacion($id10, 'Estacion 1', 5),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarEstacion($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerEstacion($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarEstaciones()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Estacion($id10, 'Estacion 1', 5),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'Estacion 1', 'capacidad' => 5],
                    'expectedView' => fn() => ['id' => $id10, 'nombre' => 'Estacion 1', 'capacidad' => 5],
                ]
            ],
            'Etiqueta' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Etiqueta::class,
                    'repo' => \App\Domain\Produccion\Repository\EtiquetaRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearEtiquetaHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarEtiquetaHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarEtiquetaHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerEtiquetaHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarEtiquetasHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearEtiqueta($idReceta, $idSuscripcion, $idPaciente, ['qr' => 'x']),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarEtiqueta($id10, $idReceta, $idSuscripcion, $idPaciente, ['qr' => 'x']),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarEtiqueta($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerEtiqueta($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarEtiquetas()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Etiqueta($id10, $idReceta, $idSuscripcion, $idPaciente, ['qr' => 'x']),
                    'expectedAfterUpdate' => fn() => ['recetaVersionId' => $idReceta, 'suscripcionId' => $idSuscripcion, 'pacienteId' => $idPaciente, 'qrPayload' => ['qr' => 'x']],
                    'expectedView' => fn() => ['id' => $id10,'receta_version_id' => $idReceta, 'suscripcion_id' => $idSuscripcion, 'paciente_id' => $idPaciente, 'qr_payload' => ['qr' => 'x']]
                ]
            ],
            'Paciente' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Paciente::class,
                    'repo' => \App\Domain\Produccion\Repository\PacienteRepositoryInterface::class,
                    'needsEventPublisher' => true,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearPacienteHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarPacienteHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarPacienteHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerPacienteHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarPacientesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearPaciente('Juan', 'CI-1', $idSuscripcion),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPaciente($id10, 'Juan', 'CI-1', $idSuscripcion),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPaciente($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPaciente($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPacientes()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Paciente($id10, 'Juan', 'CI-1', $idSuscripcion),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'Juan', 'documento' => 'CI-1', 'suscripcionId' => $idSuscripcion],
                    'expectedView' => fn() => ['id' => $id10, 'nombre' => 'Juan', 'documento' => 'CI-1', 'suscripcion_id' => $idSuscripcion]
                ]
            ],
            'Paquete' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Paquete::class,
                    'repo' => \App\Domain\Produccion\Repository\PaqueteRepositoryInterface::class,
                    'needsEventPublisher' => true,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearPaqueteHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarPaqueteHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarPaqueteHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerPaqueteHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarPaquetesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearPaquete($idEtiqueta, $idVentana, $idDireccion),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPaquete($id10, $idEtiqueta, $idVentana, $idDireccion),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPaquete($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPaquete($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPaquetes()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Paquete($id10, $idEtiqueta, $idVentana, $idDireccion),
                    'expectedAfterUpdate' => fn() => ['etiquetaId' => $idEtiqueta, 'ventanaId' => $idVentana, 'direccionId' => $idDireccion],
                    'expectedView' => fn() => ['id' => $id10, 'etiqueta_id' => $idEtiqueta, 'ventana_id' => $idVentana, 'direccion_id' => $idDireccion]
                ]
            ],
            'Porcion' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Porcion::class,
                    'repo' => \App\Domain\Produccion\Repository\PorcionRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearPorcionHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarPorcionHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarPorcionHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerPorcionHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarPorcionesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearPorcion('P1', 100),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPorcion($id10, 'P1', 100),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPorcion($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPorcion($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPorciones()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Porcion($id10, 'P1', 100),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'P1', 'pesoGr' => 100],
                    'expectedView' => fn() => ['id' => $id10,'nombre' => 'P1', 'peso_gr' => 100]
                ]
            ],
            'RecetaVersion' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\RecetaVersion::class,
                    'repo' => \App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface::class,
                    'needsEventPublisher' => true,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearRecetaVersionHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarRecetaVersionHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarRecetaVersionHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerRecetaVersionHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarRecetasVersionHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearRecetaVersion('R1', ['n' => 1], ['i' => 1], 1),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarRecetaVersion($id10, 'R1', ['n' => 1], ['i' => 1], 1),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarRecetaVersion($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerRecetaVersion($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarRecetasVersion()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\RecetaVersion($id10, 'R1', ['n' => 1], ['i' => 1], 1),
                    'expectedAfterUpdate' => fn() => [
                        'nombre' => 'R1',
                        'nutrientes' => ['n' => 1],
                        'ingredientes' => ['i' => 1],
                        'version' => 1
                    ],
                    'expectedView' => fn() => [
                        'id' => $id10,
                        'nombre' => 'R1',
                        'nutrientes' => ['n' => 1],
                        'ingredientes' => ['i' => 1],
                        'version' => 1
                    ],
                ]
            ],
            'Suscripcion' => [[
                'entity' => \App\Domain\Produccion\Entity\Suscripcion::class,
                'repo' => \App\Domain\Produccion\Repository\SuscripcionRepositoryInterface::class,
                'needsEventPublisher' => true,
                'handlers' => [
                    'crear' => \App\Application\Produccion\Handler\CrearSuscripcionHandler::class,
                    'actualizar' => \App\Application\Produccion\Handler\ActualizarSuscripcionHandler::class,
                    'eliminar' => \App\Application\Produccion\Handler\EliminarSuscripcionHandler::class,
                    'ver' => \App\Application\Produccion\Handler\VerSuscripcionHandler::class,
                    'listar' => \App\Application\Produccion\Handler\ListarSuscripcionesHandler::class
                ],
                'commands' => [
                    'crear' => fn() => new \App\Application\Produccion\Command\CrearSuscripcion('S1'),
                    'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarSuscripcion($id10, 'S1'),
                    'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarSuscripcion($id10),
                    'ver' => fn() => new \App\Application\Produccion\Command\VerSuscripcion($id10),
                    'listar' => fn() => new \App\Application\Produccion\Command\ListarSuscripciones()
                ],
                'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Suscripcion($id10, 'S1'),
                'expectedAfterUpdate' => fn() => ['nombre' => 'S1'],
                'expectedView' => fn() => ['id' => $id10, 'nombre' => 'S1']
            ]],
            'VentanaEntrega' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\VentanaEntrega::class,
                    'repo' => \App\Domain\Produccion\Repository\VentanaEntregaRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearVentanaEntregaHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarVentanaEntregaHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarVentanaEntregaHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerVentanaEntregaHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarVentanasEntregaHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearVentanaEntrega($dtDesde, $dtHasta),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarVentanaEntrega($id10, $dtDesde, $dtHasta),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarVentanaEntrega($id10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerVentanaEntrega($id10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarVentanasEntrega()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\VentanaEntrega($id10, $dtDesde, $dtHasta),
                    'expectedAfterUpdate' => fn() => ['desde' => $dtDesde, 'hasta' => $dtHasta],
                    'expectedView' => fn() => [
                        'id' => $id10, 'desde' => $dtDesde->format('Y-m-d H:i:s'), 'hasta' => $dtHasta->format('Y-m-d H:i:s')
                    ]
                ]
            ],
        ];
    }
}
