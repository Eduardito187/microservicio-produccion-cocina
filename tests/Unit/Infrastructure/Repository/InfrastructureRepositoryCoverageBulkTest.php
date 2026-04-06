<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Repository;

use App\Domain\Produccion\Aggregate\ProduccionBatch as AggregateProduccionBatch;
use App\Domain\Produccion\Entity\Calendario;
use App\Domain\Produccion\Entity\CalendarioItem;
use App\Domain\Produccion\Entity\Direccion;
use App\Domain\Produccion\Entity\Etiqueta;
use App\Domain\Produccion\Entity\InboundEvent;
use App\Domain\Produccion\Entity\Paciente;
use App\Domain\Produccion\Entity\Paquete;
use App\Domain\Produccion\Entity\Porcion;
use App\Domain\Produccion\Entity\RecetaVersion;
use App\Domain\Produccion\Entity\Suscripcion;
use App\Domain\Produccion\Entity\VentanaEntrega;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Shared\Exception\DuplicateRecordException;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Repository\CalendarioItemRepository;
use App\Infrastructure\Persistence\Repository\CalendarioRepository;
use App\Infrastructure\Persistence\Repository\DireccionRepository;
use App\Infrastructure\Persistence\Repository\EntregaEvidenciaRepository;
use App\Infrastructure\Persistence\Repository\EtiquetaRepository;
use App\Infrastructure\Persistence\Repository\InboundEventRepository;
use App\Infrastructure\Persistence\Repository\KpiRepository;
use App\Infrastructure\Persistence\Repository\PacienteRepository;
use App\Infrastructure\Persistence\Repository\PaqueteRepository;
use App\Infrastructure\Persistence\Repository\PorcionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use App\Infrastructure\Persistence\Repository\RecetaVersionRepository;
use App\Infrastructure\Persistence\Repository\SuscripcionRepository;
use App\Infrastructure\Persistence\Repository\VentanaEntregaRepository;
use DateTimeImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class InfrastructureRepositoryCoverageBulkTest
 */
class InfrastructureRepositoryCoverageBulkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    public function test_calendario_repository_crud_and_convert_date_helper(): void
    {
        $repo = new CalendarioRepository;
        $id = '11111111-1111-4111-8111-111111111111';

        $saved = $repo->save(new Calendario(
            $id,
            new DateTimeImmutable('2026-04-06'),
            'ent-1',
            'con-1',
            1
        ));

        $this->assertSame($id, $saved);
        $this->assertCount(1, $repo->list());
        $this->assertSame($id, $repo->byId($id)?->id);

        // Update existing branch in save()
        $repo->save(new Calendario($id, new DateTimeImmutable('2026-04-07'), 'ent-2', 'con-2', '2'));
        $this->assertSame('ent-2', DB::table('calendario')->where('id', $id)->value('entrega_id'));

        $convert = $this->invokePrivate($repo, 'convertDate', ['2026-04-08']);
        $this->assertInstanceOf(DateTimeImmutable::class, $convert);

        $repo->delete($id);
        $this->assertCount(0, $repo->list());

        $this->expectException(EntityNotFoundException::class);
        $repo->byId('missing-cal');
    }

    public function test_calendario_item_repository_crud_and_delete_by_calendario_id(): void
    {
        $repo = new CalendarioItemRepository;

        $a = new CalendarioItem('ci-1', 'cal-1', 'desp-1');
        $b = new CalendarioItem('ci-2', 'cal-1', 'desp-2');

        $this->assertSame('ci-1', $repo->save($a));
        $this->assertSame('ci-2', $repo->save($b));
        $this->assertCount(2, $repo->list());
        $this->assertSame('ci-1', $repo->byId('ci-1')?->id);

        $repo->delete('ci-2');
        $this->assertCount(1, $repo->list());

        $repo->deleteByCalendarioId('cal-1');
        $this->assertCount(0, $repo->list());

        $this->expectException(EntityNotFoundException::class);
        $repo->byId('missing-item');
    }

    public function test_simple_repositories_cover_crud_mapping_and_not_found(): void
    {
        // Direccion
        $direccionRepo = new DireccionRepository;
        $direccion = new Direccion('dir-1', 'Casa', 'Calle 1', 'Apto 1', 'Bogota', 'Cund', 'CO', ['lat' => 1.23]);
        $direccionRepo->save($direccion);
        $this->assertSame('dir-1', $direccionRepo->byId('dir-1')?->id);
        $this->assertCount(1, $direccionRepo->list());
        $direccionRepo->delete('dir-1');
        $this->assertCount(0, $direccionRepo->list());

        // Etiqueta
        $etiquetaRepo = new EtiquetaRepository;
        $etiqueta = new Etiqueta('et-1', 'sus-1', 'pac-1', ['qr' => 'ok']);
        $etiquetaRepo->save($etiqueta);
        $this->assertSame('et-1', $etiquetaRepo->byId('et-1')?->id);
        $this->assertCount(1, $etiquetaRepo->list());
        $etiquetaRepo->delete('et-1');

        // Paciente
        $pacienteRepo = new PacienteRepository;
        $pacienteRepo->save(new Paciente('pac-1', 'Juan', '123', 'sus-1'));
        $this->assertSame('pac-1', $pacienteRepo->byId('pac-1')?->id);
        $this->assertCount(1, $pacienteRepo->list());
        $pacienteRepo->delete('pac-1');

        // Paquete
        $paqueteRepo = new PaqueteRepository;
        $paqueteRepo->save(new Paquete('paq-1', 'et-1', 'ven-1', 'dir-1'));
        $this->assertSame('paq-1', $paqueteRepo->byId('paq-1')?->id);
        $this->assertCount(1, $paqueteRepo->list());
        $paqueteRepo->delete('paq-1');

        // Porcion
        $porcionRepo = new PorcionRepository;
        $porcionRepo->save(new Porcion('por-1', 'Porcion A', 250));
        $this->assertSame('por-1', $porcionRepo->byId('por-1')?->id);
        $this->assertCount(1, $porcionRepo->list());
        $porcionRepo->delete('por-1');

        // RecetaVersion
        $recetaRepo = new RecetaVersionRepository;
        $recetaRepo->save(new RecetaVersion('rec-1', 'Receta', ['kcal' => 10], [['agua']], 'desc', 'inst', 10));
        $this->assertSame('rec-1', $recetaRepo->byId('rec-1')?->id);
        $this->assertCount(1, $recetaRepo->list());
        $recetaRepo->delete('rec-1');

        // Suscripcion
        $susRepo = new SuscripcionRepository;
        $susRepo->save(new Suscripcion('sus-1', 'Mensual', 'pac-1', 'mensual', '2026-01-01', '2026-01-31', 'ACTIVA'));
        $this->assertSame('sus-1', $susRepo->byId('sus-1')?->id);
        $this->assertCount(1, $susRepo->list());
        $susRepo->delete('sus-1');
    }

    public function test_simple_repositories_not_found_branches(): void
    {
        $this->assertThrowsEntityNotFound(fn () => (new DireccionRepository)->byId('missing-dir'));
        $this->assertThrowsEntityNotFound(fn () => (new EtiquetaRepository)->byId('missing-et'));
        $this->assertThrowsEntityNotFound(fn () => (new PacienteRepository)->byId('missing-pac'));
        $this->assertThrowsEntityNotFound(fn () => (new PaqueteRepository)->byId('missing-paq'));
        $this->assertThrowsEntityNotFound(fn () => (new PorcionRepository)->byId('missing-por'));
        $this->assertThrowsEntityNotFound(fn () => (new RecetaVersionRepository)->byId('missing-rec'));
        $this->assertThrowsEntityNotFound(fn () => (new SuscripcionRepository)->byId('missing-sus'));
    }

    public function test_ventana_entrega_repository_crud_and_private_conversion_helper(): void
    {
        $repo = new VentanaEntregaRepository;
        $id = 'ven-1';

        $repo->save(new VentanaEntrega(
            $id,
            new DateTimeImmutable('2026-04-06 08:00:00'),
            new DateTimeImmutable('2026-04-06 10:00:00'),
            'ent-1',
            'con-1',
            '1'
        ));

        $this->assertSame($id, $repo->byId($id)?->id);
        $this->assertCount(1, $repo->list());

        $converted = $this->invokePrivate($repo, 'convertDateTime', ['2026-04-06 11:00:00']);
        $this->assertInstanceOf(DateTimeImmutable::class, $converted);

        $repo->delete($id);

        $this->expectException(EntityNotFoundException::class);
        $repo->byId('missing-ven');
    }

    public function test_inbound_event_repository_save_and_duplicate_exception_branch(): void
    {
        $repo = new InboundEventRepository;

        $eventA = new InboundEvent(
            null,
            'evt-1',
            'PacienteCreado',
            '2026-04-06T10:00:00+00:00',
            json_encode(['id' => 'p-1']) ?: '{}',
            1,
            'corr-1'
        );

        $savedId = $repo->save($eventA);
        $this->assertIsString($savedId);

        $eventB = new InboundEvent(
            null,
            'evt-1',
            'PacienteActualizado',
            '2026-04-06T11:00:00+00:00',
            json_encode(['id' => 'p-1']) ?: '{}',
            1,
            'corr-2'
        );

        $this->expectException(DuplicateRecordException::class);
        $repo->save($eventB);
    }

    public function test_kpi_repository_and_entrega_evidencia_repository_cover_upsert_flows(): void
    {
        $kpiRepo = new KpiRepository;
        $kpiRepo->increment('entrega_confirmada', 2);
        $kpiRepo->increment('entrega_confirmada', 3);

        $value = (int) DB::table('kpi_operativo')->where('name', 'entrega_confirmada')->value('value');
        $this->assertSame(5, $value);

        $evidenciaRepo = new EntregaEvidenciaRepository;
        $evidenciaRepo->upsertByEventId('evt-100', ['status' => 'confirmada', 'payload' => ['x' => 1]]);
        $evidenciaRepo->upsertByEventId('evt-100', ['status' => 'fallida', 'payload' => ['x' => 2]]);

        $this->assertSame(1, DB::table('entrega_evidencia')->where('event_id', 'evt-100')->count());
        $this->assertSame('fallida', DB::table('entrega_evidencia')->where('event_id', 'evt-100')->value('status'));
    }

    public function test_produccion_batch_repository_by_order_id_save_and_not_found_paths(): void
    {
        $repo = new ProduccionBatchRepository;

        $this->assertSame([], $repo->byOrderId(null));

        $batch = new AggregateProduccionBatch(
            'batch-1',
            'op-1',
            'prod-1',
            'por-1',
            10,
            0,
            0,
            EstadoPlanificado::PROGRAMADO,
            0.95,
            new Qty(10),
            1,
            ['ruta' => 'A']
        );

        $repo->save($batch);

        $fetched = $repo->byId('batch-1');
        $this->assertSame('batch-1', $fetched?->id);

        $byOrder = $repo->byOrderId('op-1');
        $this->assertCount(1, $byOrder);

        $this->expectException(EntityNotFoundException::class);
        $repo->byId('missing-batch');
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('calendario_item');
        Schema::dropIfExists('calendario');
        Schema::dropIfExists('direccion');
        Schema::dropIfExists('etiqueta');
        Schema::dropIfExists('paciente');
        Schema::dropIfExists('paquete');
        Schema::dropIfExists('porcion');
        Schema::dropIfExists('receta');
        Schema::dropIfExists('suscripcion');
        Schema::dropIfExists('ventana_entrega');
        Schema::dropIfExists('inbound_events');
        Schema::dropIfExists('kpi_operativo');
        Schema::dropIfExists('entrega_evidencia');
        Schema::dropIfExists('produccion_batch');

        Schema::create('calendario', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->date('fecha');
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->integer('estado')->nullable();
            $table->timestamps();
        });

        Schema::create('calendario_item', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('calendario_id');
            $table->string('item_despacho_id');
            $table->timestamps();
        });

        Schema::create('direccion', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nombre')->nullable();
            $table->string('linea1');
            $table->string('linea2')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->nullable();
            $table->json('geo')->nullable();
            $table->timestamps();
        });

        Schema::create('etiqueta', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('suscripcion_id')->nullable();
            $table->string('paciente_id')->nullable();
            $table->json('qr_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('paciente', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nombre');
            $table->string('documento')->nullable();
            $table->string('suscripcion_id')->nullable();
            $table->timestamps();
        });

        Schema::create('paquete', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('etiqueta_id')->nullable();
            $table->string('ventana_id')->nullable();
            $table->string('direccion_id')->nullable();
            $table->timestamps();
        });

        Schema::create('porcion', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nombre');
            $table->integer('peso_gr');
            $table->timestamps();
        });

        Schema::create('receta', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nombre');
            $table->json('nutrientes')->nullable();
            $table->json('ingredientes')->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('total_calories')->nullable();
            $table->timestamps();
        });

        Schema::create('suscripcion', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nombre');
            $table->string('paciente_id')->nullable();
            $table->string('tipo_servicio')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->nullable();
            $table->string('motivo_cancelacion')->nullable();
            $table->dateTime('cancelado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ventana_entrega', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->dateTime('desde');
            $table->dateTime('hasta');
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->integer('estado')->nullable();
            $table->timestamps();
        });

        Schema::create('inbound_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('event_name');
            $table->string('occurred_on')->nullable();
            $table->text('payload');
            $table->unsignedInteger('schema_version')->nullable();
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_operativo', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->integer('value')->default(0);
            $table->timestamps();
        });

        Schema::create('entrega_evidencia', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('event_id')->unique();
            $table->string('status')->nullable();
            $table->json('geo')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('produccion_batch', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('op_id')->nullable();
            $table->string('p_id')->nullable();
            $table->string('porcion_id')->nullable();
            $table->integer('cant_planificada');
            $table->integer('cant_producida')->default(0);
            $table->integer('merma_gr')->default(0);
            $table->string('estado');
            $table->decimal('rendimiento', 10, 2)->nullable();
            $table->integer('qty');
            $table->integer('posicion')->default(0);
            $table->json('ruta')->nullable();
            $table->timestamps();
        });
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }

    private function assertThrowsEntityNotFound(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Expected EntityNotFoundException was not thrown.');
        } catch (EntityNotFoundException $e) {
            $this->assertNotSame('', $e->getMessage());
        }
    }
}
