<?php

namespace Tests\Feature\Maestros;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarioItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_actualizar_y_eliminar_calendario_item(): void
    {
        $calendarioId = DB::table('calendario')->insertGetId([
            'fecha' => '2026-01-10',
            'sucursal_id' => 'SCZ-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'sku' => 'SKU-100',
            'price' => 10.0,
            'special_price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $opId = DB::table('orden_produccion')->insertGetId([
            'fecha' => '2026-01-10',
            'sucursal_id' => 'SCZ-001',
            'estado' => 'CREADA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemDespachoId = DB::table('item_despacho')->insertGetId([
            'op_id' => $opId,
            'product_id' => $productId,
            'paquete_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $create = $this->postJson(route('calendario-items.crear'), [
            'calendarioId' => $calendarioId,
            'itemDespachoId' => $itemDespachoId,
        ]);

        $create->assertCreated()->assertJsonStructure(['calendarioItemId']);
        $calendarioItemId = $create->json('calendarioItemId');

        $this->getJson(route('calendario-items.listar'))
            ->assertOk()
            ->assertJsonFragment(['id' => $calendarioItemId]);

        $this->getJson(route('calendario-items.ver', ['id' => $calendarioItemId]))
            ->assertOk()
            ->assertJsonFragment(['id' => $calendarioItemId]);

        $update = $this->putJson(route('calendario-items.actualizar', ['id' => $calendarioItemId]), [
            'calendarioId' => $calendarioId,
            'itemDespachoId' => $itemDespachoId,
        ]);

        $update->assertOk()->assertJsonPath('calendarioItemId', $calendarioItemId);

        $delete = $this->deleteJson(route('calendario-items.eliminar', ['id' => $calendarioItemId]));
        $delete->assertNoContent();
    }
}
