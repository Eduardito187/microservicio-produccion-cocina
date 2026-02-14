<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Integration\Handlers\CalendarioEntregaCreadoHandler;
use App\Application\Integration\Handlers\EntregaProgramadaHandler;
use App\Application\Integration\Handlers\DiaSinEntregaMarcadoHandler;
use App\Application\Integration\Handlers\DireccionEntregaCambiadaHandler;
use App\Infrastructure\Persistence\Model\Calendario;
use App\Infrastructure\Persistence\Model\CalendarioItem;
use App\Infrastructure\Persistence\Model\ItemDespacho;
use App\Infrastructure\Persistence\Model\Paquete;
use App\Infrastructure\Persistence\Model\Direccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * @class CalendarioInboundTest
 * @package Tests\Feature\Integration
 */
class CalendarioInboundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_calendario_entrega_creado_crea_calendario(): void
    {
        $handler = $this->app->make(CalendarioEntregaCreadoHandler::class);

        $payload = [
            'id' => 'cal-1',
            'fecha' => '2025-10-10',
        ];

        $handler->handle($payload);

        $this->assertDatabaseHas('calendario', [
            'id' => 'cal-1',
            'fecha' => '2025-10-10',
        ]);
    }

    /**
     * @return void
     */
    public function test_entrega_programada_crea_calendario_item(): void
    {
        $opId = (string) Str::uuid();
        $productId = (string) Str::uuid();
        DB::table('orden_produccion')->insert([
            'id' => $opId,
            'fecha' => '2025-10-11',
            'estado' => 'CREADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('products')->insert([
            'id' => $productId,
            'sku' => 'SKU-TEST-1',
            'price' => 1,
            'special_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Calendario::query()->create([
            'id' => 'cal-2',
            'fecha' => '2025-10-11',
        ]);
        ItemDespacho::query()->create([
            'id' => 'item-1',
            'op_id' => $opId,
            'product_id' => $productId,
        ]);

        $handler = $this->app->make(EntregaProgramadaHandler::class);

        $payload = [
            'calendarioId' => 'cal-2',
            'itemDespachoId' => 'item-1',
        ];

        $handler->handle($payload);

        $this->assertDatabaseHas('calendario_item', [
            'calendario_id' => 'cal-2',
            'item_despacho_id' => 'item-1',
        ]);
    }

    /**
     * @return void
     */
    public function test_dia_sin_entrega_borra_calendario_y_items(): void
    {
        $opId = (string) Str::uuid();
        $productId = (string) Str::uuid();
        DB::table('orden_produccion')->insert([
            'id' => $opId,
            'fecha' => '2025-10-12',
            'estado' => 'CREADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('products')->insert([
            'id' => $productId,
            'sku' => 'SKU-TEST-2',
            'price' => 1,
            'special_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Calendario::query()->create([
            'id' => 'cal-3',
            'fecha' => '2025-10-12',
        ]);
        ItemDespacho::query()->create([
            'id' => 'item-2',
            'op_id' => $opId,
            'product_id' => $productId,
        ]);
        CalendarioItem::query()->create([
            'id' => 'ci-1',
            'calendario_id' => 'cal-3',
            'item_despacho_id' => 'item-2',
        ]);

        $handler = $this->app->make(DiaSinEntregaMarcadoHandler::class);

        $payload = [
            'calendarioId' => 'cal-3',
            'fecha' => '2025-10-12',
        ];

        $handler->handle($payload);

        $this->assertDatabaseMissing('calendario', ['id' => 'cal-3']);
        $this->assertDatabaseMissing('calendario_item', ['id' => 'ci-1']);
    }

    /**
     * @return void
     */
    public function test_direccion_entrega_cambiada_actualiza_paquete(): void
    {
        Direccion::query()->create([
            'id' => 'dir-1',
            'linea1' => 'Linea 1',
        ]);
        Direccion::query()->create([
            'id' => 'dir-2',
            'linea1' => 'Linea 2',
        ]);

        Paquete::query()->create([
            'id' => 'paq-1',
            'direccion_id' => 'dir-1',
        ]);

        $handler = $this->app->make(DireccionEntregaCambiadaHandler::class);

        $payload = [
            'paqueteId' => 'paq-1',
            'direccionId' => 'dir-2',
        ];

        $handler->handle($payload);

        $this->assertDatabaseHas('paquete', [
            'id' => 'paq-1',
            'direccion_id' => 'dir-2',
        ]);
    }
}
