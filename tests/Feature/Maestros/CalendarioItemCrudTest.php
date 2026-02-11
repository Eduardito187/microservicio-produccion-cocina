<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @class CalendarioItemCrudTest
 * @package Tests\Feature\Maestros
 */
class CalendarioItemCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_crear_actualizar_y_eliminar_calendario_item(): void
    {
        $calendarioId = (string) Str::uuid();
        DB::table('calendario')->insert([
            'id' => $calendarioId,
            'fecha' => '2026-01-10',
            'sucursal_id' => 'SCZ-001',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $productId = (string) Str::uuid();
        DB::table('products')->insert([
            'id' => $productId,
            'sku' => 'SKU-100',
            'price' => 10.0,
            'special_price' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $opId = (string) Str::uuid();
        DB::table('orden_produccion')->insert([
            'id' => $opId,
            'fecha' => '2026-01-10',
            'sucursal_id' => 'SCZ-001',
            'estado' => 'CREADA',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $itemDespachoId = (string) Str::uuid();
        DB::table('item_despacho')->insert([
            'id' => $itemDespachoId,
            'op_id' => $opId,
            'product_id' => $productId,
            'paquete_id' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $create = $this->postJson(route('calendario-items.crear'), [
            'calendarioId' => $calendarioId, 'itemDespachoId' => $itemDespachoId
        ]);

        $create->assertCreated()->assertJsonStructure(['calendarioItemId']);
        $calendarioItemId = $create->json('calendarioItemId');

        $this->getJson(route('calendario-items.listar'))
            ->assertOk()->assertJsonFragment(['id' => $calendarioItemId]);
        $this->getJson(route('calendario-items.ver', ['id' => $calendarioItemId]))
            ->assertOk()->assertJsonFragment(['id' => $calendarioItemId]);

        $update = $this->putJson(route('calendario-items.actualizar', ['id' => $calendarioItemId]), [
            'calendarioId' => $calendarioId, 'itemDespachoId' => $itemDespachoId
        ]);

        $update->assertOk()->assertJsonPath('calendarioItemId', $calendarioItemId);
        $delete = $this->deleteJson(route('calendario-items.eliminar', ['id' => $calendarioItemId]));
        $delete->assertNoContent();
    }
}
