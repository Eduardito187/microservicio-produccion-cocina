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
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        $tables = ['order_item', 'produccion_batch', 'item_despacho'];

        foreach ($tables as $table) {
            $count = DB::table($table)->whereNull('op_id')->count();
            if ($count > 0) {
                throw new RuntimeException("Table {$table} has {$count} rows with NULL op_id.");
            }
        }

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('op_id', 36)->nullable(false)->change();
            });
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        $tables = ['order_item', 'produccion_batch', 'item_despacho'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('op_id', 36)->nullable()->change();
            });
        }
    }
};
