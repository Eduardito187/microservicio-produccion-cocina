<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Analytics\KpiRepositoryInterface;
use App\Application\Integration\CalendarProcessManager;
use App\Application\Integration\Handlers\ContratoCanceladoHandler;
use App\Application\Integration\Handlers\DiaSinEntregaMarcadoHandler;
use App\Application\Integration\Handlers\DireccionActualizadaHandler;
use App\Application\Integration\Handlers\DireccionCreadaHandler;
use App\Application\Integration\Handlers\EntregaConfirmadaHandler;
use App\Application\Integration\Handlers\EntregaFallidaHandler;
use App\Application\Integration\Handlers\EntregaProgramadaHandler;
use App\Application\Integration\Handlers\LogisticaPaqueteEstadoActualizadoHandler;
use App\Application\Integration\Handlers\PacienteActualizadoHandler;
use App\Application\Integration\Handlers\PacienteCreadoHandler;
use App\Application\Integration\Handlers\PaqueteEnRutaHandler;
use App\Application\Integration\Handlers\RecetaActualizadaHandler;
use App\Application\Integration\Handlers\SuscripcionActualizadaHandler;
use App\Application\Integration\Handlers\SuscripcionCreadaHandler;
use App\Application\Logistica\Repository\EntregaEvidenciaRepositoryInterface;
use App\Application\Produccion\Handler\ActualizarEstadoPaqueteDesdeLogisticaHandler;
use App\Application\Support\Transaction\Interface\TransactionManagerInterface;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Repository\CalendarioItemRepositoryInterface;
use App\Domain\Produccion\Repository\CalendarioRepositoryInterface;
use App\Domain\Produccion\Repository\DireccionRepositoryInterface;
use App\Domain\Produccion\Repository\ItemDespachoRepositoryInterface;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use App\Domain\Produccion\Repository\RecetaVersionRepositoryInterface;
use App\Domain\Produccion\Repository\SuscripcionRepositoryInterface;
use App\Domain\Shared\Exception\EntityNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @class IntegrationHandlersTest
 */
class IntegrationHandlersTest extends TestCase
{
    // ─── Helpers ──────────────────────────────────────────────────────────

    private function tx(): TransactionAggregate
    {
        $manager = new class implements TransactionManagerInterface
        {
            public function run(callable $callback): mixed
            {
                return $callback();
            }

            public function afterCommit(callable $callback): void {}
        };

        return new TransactionAggregate($manager);
    }

    // ─── SuscripcionCreadaHandler ──────────────────────────────────────────

    public function test_suscripcion_creada_handler_guarda_entidad(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Suscripcion $s) => $s->id === 'a1b2c3d4-0000-0000-0000-000000000001'));

        $handler = new SuscripcionCreadaHandler($repo, $this->tx());
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000001',
            'tipoServicio' => 'Plan 30 dias',
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000002',
            'fechaInicio' => '2026-01-01',
            'fechaFin' => '2026-02-01',
        ]);
    }

    public function test_suscripcion_creada_handler_genera_nombre_desde_tipo_servicio(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Suscripcion $s) => str_contains($s->nombre, 'Mensual')));

        $handler = new SuscripcionCreadaHandler($repo, $this->tx());
        $handler->handle([
            'suscripcionId' => 'a1b2c3d4-0000-0000-0000-000000000003',
            'tipoServicio' => 'Mensual',
        ]);
    }

    // ─── SuscripcionActualizadaHandler ─────────────────────────────────────

    public function test_suscripcion_actualizada_handler_actualiza_existente(): void
    {
        $existing = new Suscripcion('a1b2c3d4-0000-0000-0000-000000000010', 'Plan Viejo');
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save');

        $handler = new SuscripcionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000010',
            'nombre' => 'Plan Nuevo',
        ]);
    }

    public function test_suscripcion_actualizada_handler_crea_si_no_existe_y_nombre_presente(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Suscripcion $s) => $s->nombre === 'Plan Nuevo'));

        $handler = new SuscripcionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000011',
            'nombre' => 'Plan Nuevo',
        ]);
    }

    public function test_suscripcion_actualizada_handler_ignora_si_no_existe_y_sin_nombre(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->never())->method('save');

        $handler = new SuscripcionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000012',
        ]);
    }

    // ─── PacienteCreadoHandler ─────────────────────────────────────────────

    public function test_paciente_creado_handler_guarda_entidad(): void
    {
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Paciente $p) => $p->nombre === 'Ana Gomez'));

        $handler = new PacienteCreadoHandler($repo, $this->tx());
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000020',
            'nombre' => 'Ana Gomez',
            'documento' => '11111111',
            'suscripcionId' => 'a1b2c3d4-0000-0000-0000-000000000021',
        ]);
    }

    public function test_paciente_creado_handler_acepta_alias_paciente_id(): void
    {
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Paciente $p) => $p->id === 'a1b2c3d4-0000-0000-0000-000000000022'));

        $handler = new PacienteCreadoHandler($repo, $this->tx());
        $handler->handle([
            'pacienteId' => 'a1b2c3d4-0000-0000-0000-000000000022',
            'nombre' => 'Luis Torres',
        ]);
    }

    // ─── PacienteActualizadoHandler ────────────────────────────────────────

    public function test_paciente_actualizado_handler_actualiza_existente(): void
    {
        $existing = new Paciente('a1b2c3d4-0000-0000-0000-000000000030', 'Nombre Viejo');
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Paciente $p) => $p->nombre === 'Nombre Nuevo'));

        $handler = new PacienteActualizadoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000030',
            'nombre' => 'Nombre Nuevo',
        ]);
    }

    public function test_paciente_actualizado_handler_crea_si_no_existe_con_nombre(): void
    {
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Paciente $p) => $p->nombre === 'Paciente Nuevo'));

        $handler = new PacienteActualizadoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000031',
            'nombre' => 'Paciente Nuevo',
        ]);
    }

    public function test_paciente_actualizado_handler_ignora_si_no_existe_y_sin_nombre(): void
    {
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->never())->method('save');

        $handler = new PacienteActualizadoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000032',
        ]);
    }

    public function test_paciente_actualizado_handler_actualiza_documento_si_presente(): void
    {
        $existing = new Paciente('a1b2c3d4-0000-0000-0000-000000000033', 'Rosa Diaz', 'old-doc');
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Paciente $p) => $p->documento === 'new-doc'));

        $handler = new PacienteActualizadoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000033',
            'documento' => 'new-doc',
        ]);
    }

    // ─── DireccionCreadaHandler ────────────────────────────────────────────

    public function test_direccion_creada_handler_guarda_entidad(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Direccion $d) => $d->linea1 === 'Calle 123'));

        $handler = new DireccionCreadaHandler($repo, $this->tx());
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000040',
            'linea1' => 'Calle 123',
            'ciudad' => 'Bogota',
            'pais' => 'CO',
        ]);
    }

    // ─── DireccionActualizadaHandler ───────────────────────────────────────

    public function test_direccion_actualizada_handler_actualiza_existente(): void
    {
        $existing = new Direccion('a1b2c3d4-0000-0000-0000-000000000050', null, 'Old Street');
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Direccion $d) => $d->linea1 === 'New Street'));

        $handler = new DireccionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000050',
            'linea1' => 'New Street',
        ]);
    }

    public function test_direccion_actualizada_handler_crea_si_no_existe_con_linea1(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (Direccion $d) => $d->linea1 === 'Av Nueva'));

        $handler = new DireccionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000051',
            'linea1' => 'Av Nueva',
        ]);
    }

    public function test_direccion_actualizada_handler_ignora_si_no_existe_y_sin_linea1(): void
    {
        $repo = $this->createMock(DireccionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->never())->method('save');

        $handler = new DireccionActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000052',
        ]);
    }

    // ─── ContratoCanceladoHandler ──────────────────────────────────────────

    public function test_contrato_cancelado_handler_marca_suscripcion_cancelada(): void
    {
        $existing = new Suscripcion('a1b2c3d4-0000-0000-0000-000000000060', 'Plan', null, null, null, null, 'ACTIVA');
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (Suscripcion $s) {
                return $s->estado === 'CANCELADA' && $s->motivoCancelacion === 'paciente solicitó';
            }));

        $handler = new ContratoCanceladoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'contratoId' => 'a1b2c3d4-0000-0000-0000-000000000060',
            'motivoCancelacion' => 'paciente solicitó',
        ]);
    }

    public function test_contrato_cancelado_handler_ignora_si_contrato_no_encontrado(): void
    {
        $repo = $this->createMock(SuscripcionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->never())->method('save');

        $handler = new ContratoCanceladoHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'contratoId' => 'a1b2c3d4-0000-0000-0000-000000000061',
        ]);
    }

    // ─── RecetaActualizadaHandler ──────────────────────────────────────────

    public function test_receta_actualizada_handler_actualiza_existente(): void
    {
        $existing = new RecetaVersion('a1b2c3d4-0000-0000-0000-000000000070', 'Vieja Receta');
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (RecetaVersion $r) => $r->nombre === 'Nueva Receta'));

        $handler = new RecetaActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000070',
            'nombre' => 'Nueva Receta',
        ]);
    }

    public function test_receta_actualizada_handler_crea_si_no_existe_con_nombre(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->once())->method('save')
            ->with($this->callback(fn (RecetaVersion $r) => $r->nombre === 'Receta Nueva'));

        $handler = new RecetaActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000071',
            'nombre' => 'Receta Nueva',
            'nutrientes' => ['kcal' => 300],
            'ingredientes' => [['nombre' => 'sal', 'cantidad' => '5g']],
            'description' => 'Una descripcion',
            'instructions' => 'Instrucciones',
            'totalCalories' => 300,
        ]);
    }

    public function test_receta_actualizada_handler_ignora_si_no_existe_y_sin_nombre(): void
    {
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $repo->expects($this->never())->method('save');

        $handler = new RecetaActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000072',
        ]);
    }

    public function test_receta_actualizada_handler_actualiza_solo_campos_presentes(): void
    {
        $existing = new RecetaVersion(
            'a1b2c3d4-0000-0000-0000-000000000073',
            'Receta Base',
            ['kcal' => 100],
            [['sal']],
            'desc original',
            'inst original',
            100
        );
        $repo = $this->createMock(RecetaVersionRepositoryInterface::class);
        $repo->method('byId')->willReturn($existing);
        $repo->expects($this->once())->method('save')
            ->with($this->callback(function (RecetaVersion $r) {
                return $r->description === 'desc actualizada'
                    && $r->nombre === 'Receta Base';
            }));

        $handler = new RecetaActualizadaHandler($repo, $this->tx(), new NullLogger);
        $handler->handle([
            'id' => 'a1b2c3d4-0000-0000-0000-000000000073',
            'description' => 'desc actualizada',
        ]);
    }

    // ─── EntregaConfirmadaHandler ──────────────────────────────────────────

    public function test_entrega_confirmada_handler_llama_upsert_y_kpi(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->once())->method('upsertByEventId')
            ->with('event-001', $this->arrayHasKey('status'));
        $kpi->expects($this->once())->method('increment')
            ->with('entrega_confirmada', 1);

        $handler = new EntregaConfirmadaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(
            ['paqueteId' => 'pkg-001', 'fotoUrl' => 'http://x.com/foto.jpg'],
            ['event_id' => 'event-001']
        );
    }

    public function test_entrega_confirmada_handler_ignora_si_falta_event_id(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->never())->method('upsertByEventId');
        $kpi->expects($this->never())->method('increment');

        $handler = new EntregaConfirmadaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(['paqueteId' => 'pkg-001'], []);
    }

    // ─── EntregaFallidaHandler ─────────────────────────────────────────────

    public function test_entrega_fallida_handler_llama_upsert_con_status_fallida(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->once())->method('upsertByEventId')
            ->with('event-002', $this->callback(fn ($data) => $data['status'] === 'fallida'));
        $kpi->expects($this->once())->method('increment')
            ->with('entrega_fallida', 1);

        $handler = new EntregaFallidaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(
            ['paqueteId' => 'pkg-002', 'motivo' => 'Ausente'],
            ['event_id' => 'event-002']
        );
    }

    public function test_entrega_fallida_handler_ignora_si_falta_event_id(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->never())->method('upsertByEventId');

        $handler = new EntregaFallidaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(['paqueteId' => 'pkg-002'], []);
    }

    // ─── PaqueteEnRutaHandler ──────────────────────────────────────────────

    public function test_paquete_en_ruta_handler_llama_upsert_con_status_en_ruta(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->once())->method('upsertByEventId')
            ->with('event-003', $this->callback(fn ($data) => $data['status'] === 'en_ruta'));
        $kpi->expects($this->once())->method('increment')
            ->with('paquete_en_ruta', 1);

        $handler = new PaqueteEnRutaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(
            ['paqueteId' => 'pkg-003', 'rutaId' => 'ruta-01'],
            ['event_id' => 'event-003']
        );
    }

    public function test_paquete_en_ruta_handler_ignora_si_falta_event_id(): void
    {
        $evidencia = $this->createMock(EntregaEvidenciaRepositoryInterface::class);
        $kpi = $this->createMock(KpiRepositoryInterface::class);

        $evidencia->expects($this->never())->method('upsertByEventId');

        $handler = new PaqueteEnRutaHandler($evidencia, $kpi, $this->tx(), new NullLogger);
        $handler->handle(['paqueteId' => 'pkg-003'], []);
    }

    // ─── LogisticaPaqueteEstadoActualizadoHandler ──────────────────────────

    public function test_logistica_paquete_estado_handler_ignora_si_falta_event_id(): void
    {
        $commandHandler = $this->createMock(ActualizarEstadoPaqueteDesdeLogisticaHandler::class);
        $commandHandler->expects($this->never())->method('__invoke');

        $handler = new LogisticaPaqueteEstadoActualizadoHandler($commandHandler, new NullLogger);
        $handler->handle(['packageId' => 'pkg-01', 'deliveryStatus' => 'confirmada'], []);
    }

    public function test_logistica_paquete_estado_handler_ignora_si_falta_package_id(): void
    {
        $commandHandler = $this->createMock(ActualizarEstadoPaqueteDesdeLogisticaHandler::class);
        $commandHandler->expects($this->never())->method('__invoke');

        $handler = new LogisticaPaqueteEstadoActualizadoHandler($commandHandler, new NullLogger);
        $handler->handle(['deliveryStatus' => 'confirmada'], ['event_id' => 'evt-01']);
    }

    public function test_logistica_paquete_estado_handler_delega_al_command_handler(): void
    {
        $commandHandler = $this->createMock(ActualizarEstadoPaqueteDesdeLogisticaHandler::class);
        $commandHandler->expects($this->once())->method('__invoke');

        $handler = new LogisticaPaqueteEstadoActualizadoHandler($commandHandler, new NullLogger);
        $handler->handle(
            ['packageId' => 'a1b2c3d4-0000-0000-0000-000000000090', 'deliveryStatus' => 'en_ruta'],
            ['event_id' => 'evt-002']
        );
    }

    public function test_logistica_paquete_estado_handler_acepta_alias_paquete_id(): void
    {
        $commandHandler = $this->createMock(ActualizarEstadoPaqueteDesdeLogisticaHandler::class);
        $commandHandler->expects($this->once())->method('__invoke');

        $handler = new LogisticaPaqueteEstadoActualizadoHandler($commandHandler, new NullLogger);
        $handler->handle(
            ['paqueteId' => 'a1b2c3d4-0000-0000-0000-000000000091', 'status' => 'pendiente'],
            ['event_id' => 'evt-003']
        );
    }

    // ─── DiaSinEntregaMarcadoHandler ──────────────────────────────────────

    public function test_dia_sin_entrega_marcado_handler_ignora_transaccion_si_calendario_no_existe(): void
    {
        $calendarioRepo = $this->createMock(CalendarioRepositoryInterface::class);
        $itemRepo = $this->createMock(CalendarioItemRepositoryInterface::class);
        $processManager = $this->createMock(CalendarProcessManager::class);

        $calendarioRepo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $itemRepo->expects($this->never())->method('deleteByCalendarioId');
        $calendarioRepo->expects($this->never())->method('delete');
        $processManager->expects($this->once())->method('onDiaSinEntregaMarcado');

        $handler = new DiaSinEntregaMarcadoHandler(
            $calendarioRepo,
            $itemRepo,
            $this->tx(),
            $processManager
        );
        $handler->handle([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-0000000000a1',
        ]);
    }

    public function test_dia_sin_entrega_marcado_handler_elimina_items_y_calendario_si_existe(): void
    {
        $calendarioRepo = $this->createMock(CalendarioRepositoryInterface::class);
        $itemRepo = $this->createMock(CalendarioItemRepositoryInterface::class);
        $processManager = $this->createMock(CalendarProcessManager::class);

        $calendarioRepo->method('byId')->willReturn(null); // no lanza excepcion = encontrado
        $itemRepo->expects($this->once())->method('deleteByCalendarioId')
            ->with('a1b2c3d4-0000-0000-0000-0000000000a2');
        $calendarioRepo->expects($this->once())->method('delete')
            ->with('a1b2c3d4-0000-0000-0000-0000000000a2');
        $processManager->expects($this->once())->method('onDiaSinEntregaMarcado');

        $handler = new DiaSinEntregaMarcadoHandler(
            $calendarioRepo,
            $itemRepo,
            $this->tx(),
            $processManager
        );
        $handler->handle([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-0000000000a2',
        ]);
    }

    // ─── EntregaProgramadaHandler ──────────────────────────────────────────

    public function test_entrega_programada_handler_ignora_si_item_despacho_no_encontrado(): void
    {
        $itemRepo = $this->createMock(CalendarioItemRepositoryInterface::class);
        $itemDespachoRepo = $this->createMock(ItemDespachoRepositoryInterface::class);
        $processManager = $this->createMock(CalendarProcessManager::class);

        $itemDespachoRepo->method('byId')->willThrowException(new EntityNotFoundException('no existe'));
        $itemRepo->expects($this->never())->method('save');
        $processManager->expects($this->once())->method('onEntregaProgramada');

        $handler = new EntregaProgramadaHandler(
            $itemRepo,
            $this->tx(),
            $processManager,
            $itemDespachoRepo,
            new NullLogger
        );
        $handler->handle([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-0000000000b1',
            'itemDespachoId' => 'a1b2c3d4-0000-0000-0000-0000000000b2',
        ]);
    }

    public function test_entrega_programada_handler_guarda_calendario_item_cuando_item_despacho_existe(): void
    {
        $itemRepo = $this->createMock(CalendarioItemRepositoryInterface::class);
        $itemDespachoRepo = $this->createMock(ItemDespachoRepositoryInterface::class);
        $processManager = $this->createMock(CalendarProcessManager::class);

        $itemDespachoRepo->method('byId')->willReturn(null); // no lanza excepcion = encontrado
        $itemRepo->expects($this->once())->method('save')
            ->with($this->callback(fn (CalendarioItem $ci) => $ci->calendarioId === 'a1b2c3d4-0000-0000-0000-0000000000c1'
                && $ci->itemDespachoId === 'a1b2c3d4-0000-0000-0000-0000000000c2'
            ));
        $processManager->expects($this->once())->method('onEntregaProgramada');

        $handler = new EntregaProgramadaHandler(
            $itemRepo,
            $this->tx(),
            $processManager,
            $itemDespachoRepo,
            new NullLogger
        );
        $handler->handle([
            'calendarioId' => 'a1b2c3d4-0000-0000-0000-0000000000c1',
            'itemDespachoId' => 'a1b2c3d4-0000-0000-0000-0000000000c2',
        ]);
    }
}
