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
        if (!Schema::hasTable('calendario')) {
            return;
        }

        Schema::table('calendario', function (Blueprint $table) {
            if (!Schema::hasColumn('calendario', 'entrega_id')) {
                $table->string('entrega_id')->nullable()->after('fecha');
            }
            if (!Schema::hasColumn('calendario', 'contrato_id')) {
                $table->string('contrato_id')->nullable()->after('entrega_id');
            }
            if (!Schema::hasColumn('calendario', 'estado')) {
                $table->integer('estado')->nullable()->after('contrato_id');
            }
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (!Schema::hasTable('calendario')) {
            return;
        }

        Schema::table('calendario', function (Blueprint $table) {
            foreach (['estado', 'contrato_id', 'entrega_id'] as $column) {
                if (Schema::hasColumn('calendario', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
