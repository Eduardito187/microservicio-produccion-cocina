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
        if (Schema::hasTable('ventana_entrega')) {
            Schema::table('ventana_entrega', function (Blueprint $table) {
                if (! Schema::hasColumn('ventana_entrega', 'entrega_id')) {
                    $table->string('entrega_id')->nullable()->after('hasta');
                }
                if (! Schema::hasColumn('ventana_entrega', 'contrato_id')) {
                    $table->string('contrato_id')->nullable()->after('entrega_id');
                }
                if (! Schema::hasColumn('ventana_entrega', 'estado')) {
                    $table->integer('estado')->nullable()->after('contrato_id');
                }
            });
        }

        if (Schema::hasTable('item_despacho')) {
            Schema::table('item_despacho', function (Blueprint $table) {
                if (! Schema::hasColumn('item_despacho', 'paciente_id')) {
                    $table->uuid('paciente_id')->nullable()->after('paquete_id');
                }
                if (! Schema::hasColumn('item_despacho', 'direccion_id')) {
                    $table->uuid('direccion_id')->nullable()->after('paciente_id');
                }
                if (! Schema::hasColumn('item_despacho', 'ventana_entrega_id')) {
                    $table->uuid('ventana_entrega_id')->nullable()->after('direccion_id');
                }
                if (! Schema::hasColumn('item_despacho', 'entrega_id')) {
                    $table->string('entrega_id')->nullable()->after('ventana_entrega_id');
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
                foreach (['entrega_id', 'ventana_entrega_id', 'direccion_id', 'paciente_id'] as $column) {
                    if (Schema::hasColumn('item_despacho', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('ventana_entrega')) {
            Schema::table('ventana_entrega', function (Blueprint $table) {
                foreach (['estado', 'contrato_id', 'entrega_id'] as $column) {
                    if (Schema::hasColumn('ventana_entrega', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
