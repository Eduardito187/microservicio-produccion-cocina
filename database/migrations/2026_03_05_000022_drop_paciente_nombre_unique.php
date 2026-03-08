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
        if (! Schema::hasTable('paciente')) {
            return;
        }

        Schema::table('paciente', function (Blueprint $table) {
            // El nombre de un paciente NO debe ser único: distintos pacientes
            // pueden tener el mismo nombre y apellido.
            // La unicidad real está garantizada por el UUID 'id'.
            if (Schema::hasIndex('paciente', 'paciente_nombre_unique')) {
                $table->dropUnique('paciente_nombre_unique');
            }
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (! Schema::hasTable('paciente')) {
            return;
        }

        Schema::table('paciente', function (Blueprint $table) {
            $table->unique('nombre', 'paciente_nombre_unique');
        });
    }
};
