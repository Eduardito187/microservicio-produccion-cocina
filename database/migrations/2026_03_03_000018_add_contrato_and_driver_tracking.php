<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        if (Schema::hasTable('item_despacho')) {
            Schema::table('item_despacho', function (Blueprint $table) {
                if (! Schema::hasColumn('item_despacho', 'contrato_id')) {
                    $table->string('contrato_id')->nullable()->after('entrega_id');
                }
                if (! Schema::hasColumn('item_despacho', 'driver_id')) {
                    $table->uuid('driver_id')->nullable()->after('contrato_id');
                }
            });
        }

        if (Schema::hasTable('entrega_evidencia')) {
            Schema::table('entrega_evidencia', function (Blueprint $table) {
                if (! Schema::hasColumn('entrega_evidencia', 'driver_id')) {
                    $table->uuid('driver_id')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (Schema::hasTable('entrega_evidencia')) {
            Schema::table('entrega_evidencia', function (Blueprint $table) {
                if (Schema::hasColumn('entrega_evidencia', 'driver_id')) {
                    $table->dropColumn('driver_id');
                }
            });
        }

        if (Schema::hasTable('item_despacho')) {
            Schema::table('item_despacho', function (Blueprint $table) {
                foreach (['driver_id', 'contrato_id'] as $column) {
                    if (Schema::hasColumn('item_despacho', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
