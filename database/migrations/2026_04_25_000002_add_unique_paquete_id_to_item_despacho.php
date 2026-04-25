<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows keeping the one with delivery data (or the most recent)
        DB::statement('
            DELETE id1 FROM item_despacho id1
            INNER JOIN item_despacho id2
                ON id1.paquete_id = id2.paquete_id
                AND id1.id <> id2.id
            WHERE id1.paquete_id IS NOT NULL
              AND (
                id1.delivery_status IS NULL AND id2.delivery_status IS NOT NULL
                OR (
                    (id1.delivery_status IS NULL OR id1.delivery_status = id2.delivery_status)
                    AND id1.created_at < id2.created_at
                )
              )
        ');

        Schema::table('item_despacho', function (Blueprint $table) {
            $table->unique('paquete_id', 'item_despacho_paquete_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('item_despacho', function (Blueprint $table) {
            $table->dropUnique('item_despacho_paquete_id_unique');
        });
    }
};
