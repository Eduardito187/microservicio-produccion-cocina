<?php

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
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;


class MaestrosHandlersCrudTest extends TestCase
{
    /**
     * @return TransactionAggregate
     */
    private function tx(): TransactionAggregate
    {
        $transactionManager = new class implements TransactionManagerInterface {
            public function run(callable $callback): mixed {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($transactionManager);
    }

    /**
     * @return void
     */
    public function test_calendario_crud_handlers_invocan_repositorio_y_mapean_respuesta(): void
    {
        $repository = $this->createMock(CalendarioRepositoryInterface::class);
        // Crear
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Calendario $calendario): bool {
                return $calendario->id === null && $calendario->fecha->format('Y-m-d') === '2026-01-10' && $calendario->sucursalId === 'SCZ-001';
            }))->willReturn(10);
        $crear = new CrearCalendarioHandler($repository, $this->tx());
        $id = $crear(new CrearCalendario(new DateTimeImmutable('2026-01-10'), 'SCZ-001'));
        $this->assertSame(10, $id);
        // Actualizar
        $existing = new Calendario(10, new DateTimeImmutable('2026-01-10'), 'SCZ-001');
        $repository2 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository2->method('byId')->with(10)->willReturn($existing);
        $repository2->expects($this->once())->method('save')->willReturn(10);
        $actualizar = new ActualizarCalendarioHandler($repository2, $this->tx());
        $actualizadoId = $actualizar(new ActualizarCalendario(10, new DateTimeImmutable('2026-01-11'), 'SCZ-002'));
        $this->assertSame(10, $actualizadoId);
        $this->assertSame('2026-01-11', $existing->fecha->format('Y-m-d'));
        $this->assertSame('SCZ-002', $existing->sucursalId);
        // Ver
        $repository3 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository3->method('byId')->with(10)->willReturn($existing);
        $ver = new VerCalendarioHandler($repository3, $this->tx());
        $data = $ver(new VerCalendario(10));
        $this->assertSame(['id' => 10, 'fecha' => '2026-01-11', 'sucursal_id' => 'SCZ-002'], $data);
        // Listar
        $repository4 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository4->method('list')->willReturn([$existing]);
        $listar = new ListarCalendariosHandler($repository4, $this->tx());
        $list = $listar(new ListarCalendarios());
        $this->assertCount(1, $list);
        $this->assertSame(10, $list[0]['id']);
        // Eliminar
        $repository5 = $this->createMock(CalendarioRepositoryInterface::class);
        $repository5->method('byId')->with(10)->willReturn($existing);
        $repository5->expects($this->once())->method('delete')->with(10);
        $eliminar = new EliminarCalendarioHandler($repository5, $this->tx());
        $eliminar(new EliminarCalendario(10));
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function test_direccion_crud_handlers_invocan_repositorio_y_mapean_respuesta(): void
    {
        $repository = $this->createMock(DireccionRepositoryInterface::class);
        // Crear
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function (Direccion $direccion): bool {
                return $direccion->id === null && $direccion->nombre === 'Casa' && $direccion->linea1 === 'Av. Siempre Viva 123'
                    && $direccion->ciudad === 'SCZ' && $direccion->geo === ['lat' => -17.78, 'lng' => -63.18];
            }))->willReturn(20);
        $crear = new CrearDireccionHandler($repository, $this->tx());
        $id = $crear(new CrearDireccion(
            'Casa','Av. Siempre Viva 123',null,'SCZ',null,'BO',['lat' => -17.78, 'lng' => -63.18]
        ));
        $this->assertSame(20, $id);
        // Actualizar
        $existing = new Direccion(20, 'Casa', 'Av. Siempre Viva 123', null, 'SCZ', null, 'BO', ['lat' => -17.78, 'lng' => -63.18]);
        $repository2 = $this->createMock(DireccionRepositoryInterface::class);
        $repository2->method('byId')->with(20)->willReturn($existing);
        $repository2->expects($this->once())->method('save')->willReturn(20);
        $actualizar = new ActualizarDireccionHandler($repository2, $this->tx());
        $actualizadoId = $actualizar(new ActualizarDireccion(
            20, 'Oficina', 'Calle 1', 'Piso 2', 'LPZ', 'Murillo', 'BO', null
        ));
        $this->assertSame(20, $actualizadoId);
        $this->assertSame('Oficina', $existing->nombre);
        $this->assertSame('LPZ', $existing->ciudad);
        // Ver
        $repository3 = $this->createMock(DireccionRepositoryInterface::class);
        $repository3->method('byId')->with(20)->willReturn($existing);
        $ver = new VerDireccionHandler($repository3, $this->tx());
        $data = $ver(new VerDireccion(20));
        $this->assertSame(20, $data['id']);
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
        $repository5->method('byId')->with(20)->willReturn($existing);
        $repository5->expects($this->once())->method('delete')->with(20);
        $eliminar = new EliminarDireccionHandler($repository5, $this->tx());
        $eliminar(new EliminarDireccion(20));
        $this->assertTrue(true);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_crear_handler_llama_save_y_devuelve_id(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('save')
            ->with($this->isInstanceOf($data['entity']))->willReturn(123);
        $handler = new ($data['handlers']['crear'])($repository, $this->tx());
        $id = $handler($data['commands']['crear']());

        $this->assertSame(123, $id);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_actualizar_handler_hace_byId_modifica_y_save(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $entity = $data['makeEntity']();
        $repository->expects($this->once())->method('byId')->with(10)->willReturn($entity);
        $repository->expects($this->once())->method('save')
            ->with($this->callback(function ($saved) use ($data): bool {
                if (!($saved instanceof $data['entity'])) return false;

                foreach ($data['expectedAfterUpdate']() as $prop => $expected) {
                    if (!property_exists($saved, $prop)) return false;
                    if ($saved->$prop !== $expected) return false;
                }

                return true;
            }))->willReturn(10);
        $handler = new ($data['handlers']['actualizar'])($repository, $this->tx());
        $id = $handler($data['commands']['actualizar']());

        $this->assertSame(10, $id);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_eliminar_handler_hace_byId_y_delete(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('byId')->with(10)->willReturn($data['makeEntity']());
        $repository->expects($this->once())->method('delete')->with(10);
        $handler = new ($data['handlers']['eliminar'])($repository, $this->tx());
        $handler($data['commands']['eliminar']());

        $this->assertTrue(true);
    }

    /**
     * @dataProvider maestrosProvider
     */
    public function test_ver_handler_mapea_correcto(array $data): void
    {
        $repository = $this->createMock($data['repo']);
        $repository->expects($this->once())->method('byId')->with(10)->willReturn($data['makeEntity']());
        $handler = new ($data['handlers']['ver'])($repository, $this->tx());
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
        $handler = new ($data['handlers']['listar'])($repository, $this->tx());
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

        return [
            'CalendarioItem' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\CalendarioItem::class,
                    'repo' => \App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearCalendarioItemHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarCalendarioItemHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarCalendarioItemHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerCalendarioItemHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarCalendarioItemsHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearCalendarioItem(1, 2),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarCalendarioItem(10, 1, 2),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarCalendarioItem(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerCalendarioItem(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarCalendarioItems()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\CalendarioItem(10, 1, 2),
                    'expectedAfterUpdate' => fn() => ['calendarioId' => 1, 'itemDespachoId' => 2],
                    'expectedView' => fn() => ['id' => 10, 'calendario_id' => 1, 'item_despacho_id' => 2]
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
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarEstacion(10, 'Estacion 1', 5),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarEstacion(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerEstacion(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarEstaciones()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Estacion(10, 'Estacion 1', 5),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'Estacion 1', 'capacidad' => 5],
                    'expectedView' => fn() => ['id' => 10, 'nombre' => 'Estacion 1', 'capacidad' => 5],
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
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearEtiqueta(1, 2, 3, ['qr' => 'x']),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarEtiqueta(10, 1, 2, 3, ['qr' => 'x']),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarEtiqueta(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerEtiqueta(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarEtiquetas()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Etiqueta(10, 1, 2, 3, ['qr' => 'x']),
                    'expectedAfterUpdate' => fn() => ['recetaVersionId' => 1, 'suscripcionId' => 2, 'pacienteId' => 3, 'qrPayload' => ['qr' => 'x']],
                    'expectedView' => fn() => ['id' => 10,'receta_version_id' => 1, 'suscripcion_id' => 2, 'paciente_id' => 3, 'qr_payload' => ['qr' => 'x']]
                ]
            ],
            'Paciente' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Paciente::class,
                    'repo' => \App\Domain\Produccion\Repository\PacienteRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearPacienteHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarPacienteHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarPacienteHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerPacienteHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarPacientesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearPaciente('Juan', 'CI-1', 2),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPaciente(10, 'Juan', 'CI-1', 2),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPaciente(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPaciente(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPacientes()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Paciente(10, 'Juan', 'CI-1', 2),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'Juan', 'documento' => 'CI-1', 'suscripcionId' => 2],
                    'expectedView' => fn() => ['id' => 10, 'nombre' => 'Juan', 'documento' => 'CI-1', 'suscripcion_id' => 2]
                ]
            ],
            'Paquete' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\Paquete::class,
                    'repo' => \App\Domain\Produccion\Repository\PaqueteRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearPaqueteHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarPaqueteHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarPaqueteHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerPaqueteHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarPaquetesHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearPaquete(1, 2, 3),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPaquete(10, 1, 2, 3),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPaquete(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPaquete(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPaquetes()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Paquete(10, 1, 2, 3),
                    'expectedAfterUpdate' => fn() => ['etiquetaId' => 1, 'ventanaId' => 2, 'direccionId' => 3],
                    'expectedView' => fn() => ['id' => 10, 'etiqueta_id' => 1, 'ventana_id' => 2, 'direccion_id' => 3]
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
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarPorcion(10, 'P1', 100),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarPorcion(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerPorcion(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarPorciones()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Porcion(10, 'P1', 100),
                    'expectedAfterUpdate' => fn() => ['nombre' => 'P1', 'pesoGr' => 100],
                    'expectedView' => fn() => ['id' => 10,'nombre' => 'P1', 'peso_gr' => 100]
                ]
            ],
            'RecetaVersion' => [
                [
                    'entity' => \App\Domain\Produccion\Entity\RecetaVersion::class,
                    'repo' => \App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface::class,
                    'handlers' => [
                        'crear' => \App\Application\Produccion\Handler\CrearRecetaVersionHandler::class,
                        'actualizar' => \App\Application\Produccion\Handler\ActualizarRecetaVersionHandler::class,
                        'eliminar' => \App\Application\Produccion\Handler\EliminarRecetaVersionHandler::class,
                        'ver' => \App\Application\Produccion\Handler\VerRecetaVersionHandler::class,
                        'listar' => \App\Application\Produccion\Handler\ListarRecetasVersionHandler::class
                    ],
                    'commands' => [
                        'crear' => fn() => new \App\Application\Produccion\Command\CrearRecetaVersion('R1', ['n' => 1], ['i' => 1], 1),
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarRecetaVersion(10, 'R1', ['n' => 1], ['i' => 1], 1),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarRecetaVersion(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerRecetaVersion(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarRecetasVersion()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\RecetaVersion(10, 'R1', ['n' => 1], ['i' => 1], 1),
                    'expectedAfterUpdate' => fn() => [
                        'nombre' => 'R1',
                        'nutrientes' => ['n' => 1],
                        'ingredientes' => ['i' => 1],
                        'version' => 1
                    ],
                    'expectedView' => fn() => [
                        'id' => 10,
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
                'handlers' => [
                    'crear' => \App\Application\Produccion\Handler\CrearSuscripcionHandler::class,
                    'actualizar' => \App\Application\Produccion\Handler\ActualizarSuscripcionHandler::class,
                    'eliminar' => \App\Application\Produccion\Handler\EliminarSuscripcionHandler::class,
                    'ver' => \App\Application\Produccion\Handler\VerSuscripcionHandler::class,
                    'listar' => \App\Application\Produccion\Handler\ListarSuscripcionesHandler::class
                ],
                'commands' => [
                    'crear' => fn() => new \App\Application\Produccion\Command\CrearSuscripcion('S1'),
                    'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarSuscripcion(10, 'S1'),
                    'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarSuscripcion(10),
                    'ver' => fn() => new \App\Application\Produccion\Command\VerSuscripcion(10),
                    'listar' => fn() => new \App\Application\Produccion\Command\ListarSuscripciones()
                ],
                'makeEntity' => fn() => new \App\Domain\Produccion\Entity\Suscripcion(10, 'S1'),
                'expectedAfterUpdate' => fn() => ['nombre' => 'S1'],
                'expectedView' => fn() => ['id' => 10, 'nombre' => 'S1']
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
                        'actualizar' => fn() => new \App\Application\Produccion\Command\ActualizarVentanaEntrega(10, $dtDesde, $dtHasta),
                        'eliminar' => fn() => new \App\Application\Produccion\Command\EliminarVentanaEntrega(10),
                        'ver' => fn() => new \App\Application\Produccion\Command\VerVentanaEntrega(10),
                        'listar' => fn() => new \App\Application\Produccion\Command\ListarVentanasEntrega()
                    ],
                    'makeEntity' => fn() => new \App\Domain\Produccion\Entity\VentanaEntrega(10, $dtDesde, $dtHasta),
                    'expectedAfterUpdate' => fn() => ['desde' => $dtDesde, 'hasta' => $dtHasta],
                    'expectedView' => fn() => [
                        'id' => 10, 'desde' => $dtDesde->format('Y-m-d H:i:s'), 'hasta' => $dtHasta->format('Y-m-d H:i:s')
                    ]
                ]
            ],
        ];
    }
}