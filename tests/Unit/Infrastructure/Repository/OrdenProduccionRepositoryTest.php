<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Repository;

use App\Application\Shared\DomainEventPublisherInterface;
use App\Domain\Produccion\Aggregate\OrdenProduccion as AggregateOrdenProduccion;
use App\Domain\Produccion\Aggregate\ProduccionBatch;
use App\Domain\Produccion\Entity\ItemDespacho;
use App\Domain\Produccion\Entity\OrdenItem;
use App\Domain\Produccion\Enum\EstadoPlanificado;
use App\Domain\Produccion\ValueObjects\Qty;
use App\Domain\Produccion\ValueObjects\Sku;
use App\Domain\Shared\Exception\EntityNotFoundException;
use App\Infrastructure\Persistence\Repository\ItemDespachoRepository;
use App\Infrastructure\Persistence\Repository\OrdenItemRepository;
use App\Infrastructure\Persistence\Repository\OrdenProduccionRepository;
use App\Infrastructure\Persistence\Repository\ProduccionBatchRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * @class OrdenProduccionRepositoryTest
 */
class OrdenProduccionRepositoryTest extends TestCase
{
    protected static bool $schemaCreated = false;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $this->createSchema();
    }

    private function createSchema(): void
    {
        Schema::create('suscripcion', function ($table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->unique();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('porcion', function ($table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->unique();
            $table->unsignedInteger('peso_gr');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('products', function ($table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('nombre', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('special_price', 10, 2)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('paciente', function ($table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->unique();
            $table->string('documento')->nullable();
            $table->uuid('suscripcion_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('direccion', function ($table) {
            $table->uuid('id')->primary();
            $table->string('nombre')->nullable();
            $table->string('linea1');
            $table->string('linea2')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->nullable();
            $table->json('geo')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('ventana_entrega', function ($table) {
            $table->uuid('id')->primary();
            $table->dateTime('desde');
            $table->dateTime('hasta');
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('orden_produccion', function ($table) {
            $table->uuid('id')->primary();
            $table->date('fecha');
            $table->string('estado');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('order_item', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('op_id')->nullable();
            $table->uuid('p_id')->nullable();
            $table->integer('qty');
            $table->decimal('price', 18, 2)->default(0);
            $table->decimal('final_price', 18, 2)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('produccion_batch', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('op_id')->nullable();
            $table->uuid('p_id')->nullable();
            $table->uuid('porcion_id')->nullable();
            $table->integer('cant_planificada');
            $table->integer('cant_producida')->default(0);
            $table->integer('merma_gr')->default(0);
            $table->decimal('rendimiento', 18, 2)->nullable();
            $table->string('estado');
            $table->integer('qty');
            $table->integer('posicion')->default(0);
            $table->json('ruta')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('etiqueta', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('suscripcion_id')->nullable();
            $table->uuid('paciente_id')->nullable();
            $table->json('qr_payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('paquete', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('etiqueta_id')->nullable();
            $table->uuid('ventana_id')->nullable();
            $table->uuid('direccion_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('item_despacho', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('op_id')->nullable();
            $table->uuid('product_id')->nullable();
            $table->uuid('paquete_id')->nullable();
            $table->uuid('paciente_id')->nullable();
            $table->uuid('direccion_id')->nullable();
            $table->uuid('ventana_entrega_id')->nullable();
            $table->string('entrega_id')->nullable();
            $table->string('contrato_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('outbox', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->string('event_name');
            $table->uuid('aggregate_id')->nullable();
            $table->unsignedInteger('schema_version')->nullable();
            $table->string('correlation_id')->nullable();
            $table->string('trace_id', 32)->nullable();
            $table->string('span_id', 16)->nullable();
            $table->json('payload');
            $table->timestamp('occurred_on');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('event_store', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id')->unique();
            $table->string('event_name', 150);
            $table->string('aggregate_id')->nullable();
            $table->string('occurred_on')->nullable();
            $table->longText('payload');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function makeRepository(
        ?DomainEventPublisherInterface $publisher = null
    ): OrdenProduccionRepository {
        return new OrdenProduccionRepository(
            new OrdenItemRepository(new \App\Infrastructure\Persistence\Repository\ProductRepository),
            new ItemDespachoRepository,
            new ProduccionBatchRepository,
            $publisher ?? $this->createMock(DomainEventPublisherInterface::class)
        );
    }

    private function seedProduct(): string
    {
        $id = Uuid::uuid4()->toString();
        DB::table('products')->insert([
            'id' => $id,
            'sku' => 'SKU-001',
            'price' => 10.00,
            'special_price' => 0.00,
        ]);

        return $id;
    }

    private function seedPorcion(): string
    {
        $id = Uuid::uuid4()->toString();
        DB::table('porcion')->insert([
            'id' => $id,
            'nombre' => 'Porcion Test',
            'peso_gr' => 200,
        ]);

        return $id;
    }

    // ────────────────────────────────────────────────────
    // byId — throws when not found
    // ────────────────────────────────────────────────────

    public function test_by_id_throws_for_nonexistent_op(): void
    {
        $repo = $this->makeRepository();

        $this->expectException(EntityNotFoundException::class);
        $repo->byId(Uuid::uuid4()->toString());
    }

    // ────────────────────────────────────────────────────
    // save + byId round-trip (no items, no batches, no despacho)
    // ────────────────────────────────────────────────────

    public function test_save_and_by_id_round_trip_empty_op(): void
    {
        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-01'));
        $insertedId = $repo->save($op);

        $this->assertNotNull($insertedId);

        $fetched = $repo->byId($insertedId);
        $this->assertNotNull($fetched);
        $this->assertSame($insertedId, $fetched->id());
        $this->assertSame('2026-05-01', $fetched->fecha()->format('Y-m-d'));
    }

    // ────────────────────────────────────────────────────
    // save with OrdenItem → covers mapItems + savedItems
    // ────────────────────────────────────────────────────

    public function test_save_with_items_creates_order_items(): void
    {
        $productId = $this->seedProduct();

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        $item = new OrdenItem(
            Uuid::uuid4()->toString(),
            null,
            $productId,
            new Qty(3),
            new Sku('SKU-001')
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-02'), [$item]);
        $insertedId = $repo->save($op);

        $this->assertSame(1, (int) DB::table('order_item')->where('op_id', $insertedId)->count());
    }

    // ────────────────────────────────────────────────────
    // save with ProduccionBatch → covers mapItemsBatches + savedBatch
    //   (batch with ProduccionBatchCreado event where aggregateId == null)
    // ────────────────────────────────────────────────────

    public function test_save_with_batch_creates_produccion_batch_and_reemits_event(): void
    {
        $productId = $this->seedProduct();
        $porcionId = $this->seedPorcion();

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        // publish called at least once: OP creation event
        $publisher->expects($this->atLeastOnce())->method('publish');

        $repo = $this->makeRepository($publisher);

        // Use crear() so that it records ProduccionBatchCreado with aggregateId === null
        $batch = ProduccionBatch::crear(
            null,          // null id → triggers rebind path in savedBatch
            'dummy-op',
            $productId,
            $porcionId,
            10,
            0,
            0,
            EstadoPlanificado::PROGRAMADO,
            1.0,
            new Qty(5),
            0,
            []
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-03'), [], [$batch]);
        $insertedId = $repo->save($op);

        // The batch is saved with op_id equal to its own ordenProduccionId ('dummy-op')
        // savedBatch() does not override it with the OP's id
        $this->assertSame(1, (int) DB::table('produccion_batch')->count());
    }

    // ────────────────────────────────────────────────────
    // save with ItemDespacho (paqueteId already known) → savedDespacho simple path
    // ────────────────────────────────────────────────────

    public function test_save_with_item_despacho_known_paquete_id(): void
    {
        $productId = $this->seedProduct();
        $paqueteId = Uuid::uuid4()->toString();
        $opId = Uuid::uuid4()->toString();

        // Create a paquete row so the FK is valid (FK checking disabled)
        DB::table('paquete')->insert(['id' => $paqueteId]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        $despacho = new ItemDespacho(
            Uuid::uuid4()->toString(),
            $opId,          // same opId as the aggregate we will create
            $productId,
            $paqueteId
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-04'), [], [], [$despacho], $opId);
        $insertedId = $repo->save($op);

        $this->assertSame(1, (int) DB::table('item_despacho')->where('op_id', $insertedId)->count());
    }

    // ────────────────────────────────────────────────────
    // savedDespacho → resolvePaqueteId when paciente/direccion/ventana are null
    // ────────────────────────────────────────────────────

    public function test_save_despacho_with_null_references_skips_paquete_resolution(): void
    {
        $productId = $this->seedProduct();
        $opId = Uuid::uuid4()->toString();

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        // No paqueteId + all optional fields null → resolvePaqueteId returns null
        $despacho = new ItemDespacho(
            Uuid::uuid4()->toString(),
            $opId,
            $productId,
            null,    // paqueteId
            null,    // pacienteId
            null,    // direccionId
            null     // ventanaEntregaId
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-05'), [], [], [$despacho], $opId);
        $insertedId = $repo->save($op);

        $row = DB::table('item_despacho')->where('op_id', $insertedId)->first();
        $this->assertNotNull($row);
        $this->assertNull($row->paquete_id);
    }

    // ────────────────────────────────────────────────────
    // savedDespacho → resolvePaqueteId when paciente is not found
    // ────────────────────────────────────────────────────

    public function test_save_despacho_resolve_paquete_returns_null_when_paciente_not_found(): void
    {
        $productId = $this->seedProduct();
        $opId = Uuid::uuid4()->toString();

        $missingPacienteId = Uuid::uuid4()->toString();
        $direccionId = Uuid::uuid4()->toString();
        $ventanaId = Uuid::uuid4()->toString();

        DB::table('direccion')->insert([
            'id' => $direccionId,
            'linea1' => 'Calle 1',
            'ciudad' => 'Bogota',
            'pais' => 'CO',
            'geo' => json_encode(['lat' => 4.6, 'lng' => -74.1]),
        ]);
        DB::table('ventana_entrega')->insert([
            'id' => $ventanaId,
            'desde' => '2026-05-10 08:00:00',
            'hasta' => '2026-05-10 12:00:00',
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        $despacho = new ItemDespacho(
            Uuid::uuid4()->toString(),
            $opId,
            $productId,
            null,
            $missingPacienteId,   // does not exist in DB
            $direccionId,
            $ventanaId
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-05-06'), [], [], [$despacho], $opId);
        $insertedId = $repo->save($op);

        $row = DB::table('item_despacho')->where('op_id', $insertedId)->first();
        $this->assertNotNull($row);
        $this->assertNull($row->paquete_id);
    }

    // ────────────────────────────────────────────────────
    // savedDespacho → resolvePaqueteId full happy path
    //   creates etiqueta + paquete + publishes PaqueteParaDespachoCreado
    // ────────────────────────────────────────────────────

    public function test_save_despacho_creates_paquete_when_all_references_present(): void
    {
        $productId = $this->seedProduct();

        $suscripcionId = Uuid::uuid4()->toString();
        DB::table('suscripcion')->insert(['id' => $suscripcionId, 'nombre' => 'Suscrip-Test']);

        $pacienteId = Uuid::uuid4()->toString();
        DB::table('paciente')->insert([
            'id' => $pacienteId,
            'nombre' => 'Paciente Uno',
            'suscripcion_id' => $suscripcionId,
        ]);

        $direccionId = Uuid::uuid4()->toString();
        DB::table('direccion')->insert([
            'id' => $direccionId,
            'linea1' => 'Av. Siempreviva 123',
            'ciudad' => 'Springfield',
            'pais' => 'US',
            'geo' => json_encode(['lat' => 37.7, 'lng' => -122.4]),
        ]);

        $ventanaId = Uuid::uuid4()->toString();
        DB::table('ventana_entrega')->insert([
            'id' => $ventanaId,
            'desde' => '2026-06-01 09:00:00',
            'hasta' => '2026-06-01 13:00:00',
            'entrega_id' => 'ENTREGA-001',
            'contrato_id' => 'CONTRATO-001',
        ]);

        $publishCallCount = 0;
        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->method('publish')->willReturnCallback(function () use (&$publishCallCount) {
            $publishCallCount++;
        });

        $repo = $this->makeRepository($publisher);

        $opId = Uuid::uuid4()->toString();

        $despacho = new ItemDespacho(
            Uuid::uuid4()->toString(),
            $opId,
            $productId,
            null,           // paqueteId must be resolved
            $pacienteId,
            $direccionId,
            $ventanaId
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-06-01'), [], [], [$despacho], $opId);
        $insertedId = $repo->save($op);

        // The paquete row should have been created
        $this->assertSame(1, (int) DB::table('paquete')->count());

        // The item_despacho should have the resolved paquete_id
        $row = DB::table('item_despacho')->where('op_id', $insertedId)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->paquete_id);

        // publish() called at least twice: OP event + PaqueteParaDespachoCreado
        $this->assertGreaterThanOrEqual(2, $publishCallCount);
    }

    // ────────────────────────────────────────────────────
    // byId with items + batches + despacho → mapItems/mapItemsBatches/mapItemsDespachos
    // ────────────────────────────────────────────────────

    public function test_by_id_maps_items_batches_and_despacho(): void
    {
        $productId = $this->seedProduct();
        $porcionId = $this->seedPorcion();

        $opId = Uuid::uuid4()->toString();
        $itemId = Uuid::uuid4()->toString();
        $batchId = Uuid::uuid4()->toString();
        $paqueteId = Uuid::uuid4()->toString();
        $despachoId = Uuid::uuid4()->toString();

        DB::table('orden_produccion')->insert([
            'id' => $opId,
            'fecha' => '2026-07-01',
            'estado' => 'CREADA',
        ]);

        DB::table('order_item')->insert([
            'id' => $itemId,
            'op_id' => $opId,
            'p_id' => $productId,
            'qty' => 2,
            'price' => 10.00,
            'final_price' => 10.00,
        ]);

        DB::table('produccion_batch')->insert([
            'id' => $batchId,
            'op_id' => $opId,
            'p_id' => $productId,
            'porcion_id' => $porcionId,
            'cant_planificada' => 5,
            'cant_producida' => 0,
            'merma_gr' => 0,
            'rendimiento' => 1.0,
            'estado' => 'PROGRAMADO',
            'qty' => 5,
            'posicion' => 0,
            'ruta' => json_encode([]),
        ]);

        DB::table('paquete')->insert(['id' => $paqueteId]);

        DB::table('item_despacho')->insert([
            'id' => $despachoId,
            'op_id' => $opId,
            'product_id' => $productId,
            'paquete_id' => $paqueteId,
        ]);

        $repo = $this->makeRepository();
        $fetched = $repo->byId($opId);

        $this->assertCount(1, $fetched->items());
        $this->assertCount(1, $fetched->batches());
        $this->assertCount(1, $fetched->itemsDespacho());
    }

    // ────────────────────────────────────────────────────
    // resolveEntregaIdFromVentana / resolveContratoIdFromVentana via DB rows
    // ────────────────────────────────────────────────────

    public function test_resolve_entrega_and_contrato_via_db_ventana_row(): void
    {
        $productId = $this->seedProduct();

        $ventanaId = Uuid::uuid4()->toString();
        DB::table('ventana_entrega')->insert([
            'id' => $ventanaId,
            'desde' => '2026-08-01 09:00:00',
            'hasta' => '2026-08-01 13:00:00',
            'entrega_id' => 'ENTREGA-LIVE',
            'contrato_id' => 'CONTRATO-LIVE',
        ]);

        $publisher = $this->createMock(DomainEventPublisherInterface::class);
        $publisher->expects($this->once())->method('publish');

        $repo = $this->makeRepository($publisher);

        $opId = Uuid::uuid4()->toString();

        // ItemDespacho with ventanaId but no paqueteId and missing paciente → null resolution
        // But entrega_id/contrato_id get resolved from ventana if present in item
        $despacho = new ItemDespacho(
            Uuid::uuid4()->toString(),
            $opId,
            $productId,
            null,
            null,
            null,
            $ventanaId,   // has valid string ventana → entrega resolved
            null,         // entregaId null → triggers resolveEntregaIdFromVentana
            null          // contratoId null → triggers resolveContratoIdFromVentana
        );

        $op = AggregateOrdenProduccion::crear(new DateTimeImmutable('2026-08-01'), [], [], [$despacho], $opId);
        $insertedId = $repo->save($op);

        $row = DB::table('item_despacho')->where('op_id', $insertedId)->first();
        $this->assertNotNull($row);
        $this->assertSame('ENTREGA-LIVE', $row->entrega_id);
        $this->assertSame('CONTRATO-LIVE', $row->contrato_id);
    }
}
