<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = ['order_item', 'produccion_batch', 'item_despacho'];

        foreach ($tables as $table) {
            $count = DB::table($table)->whereNull('op_id')->count();
            if ($count > 0) {
                throw new RuntimeException("Table {$table} has {$count} rows with NULL op_id.");
            }
        }

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY op_id CHAR(36) NOT NULL");
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = ['order_item', 'produccion_batch', 'item_despacho'];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY op_id CHAR(36) NULL");
        }
    }
};
