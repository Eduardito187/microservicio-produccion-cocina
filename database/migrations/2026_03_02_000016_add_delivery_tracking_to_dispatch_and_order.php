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
                if (!Schema::hasColumn('item_despacho', 'delivery_status')) {
                    $table->string('delivery_status', 30)->nullable()->after('paquete_id');
                }
                if (!Schema::hasColumn('item_despacho', 'delivery_occurred_on')) {
                    $table->dateTime('delivery_occurred_on')->nullable()->after('delivery_status');
                }
            });
        }

        if (Schema::hasTable('orden_produccion')) {
            Schema::table('orden_produccion', function (Blueprint $table) {
                if (!Schema::hasColumn('orden_produccion', 'entrega_completada_at')) {
                    $table->dateTime('entrega_completada_at')->nullable()->after('estado');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (Schema::hasTable('item_despacho')) {
            Schema::table('item_despacho', function (Blueprint $table) {
                foreach (['delivery_occurred_on', 'delivery_status'] as $column) {
                    if (Schema::hasColumn('item_despacho', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('orden_produccion')) {
            Schema::table('orden_produccion', function (Blueprint $table) {
                if (Schema::hasColumn('orden_produccion', 'entrega_completada_at')) {
                    $table->dropColumn('entrega_completada_at');
                }
            });
        }
    }
};

