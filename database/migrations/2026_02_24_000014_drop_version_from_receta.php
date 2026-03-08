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
        $tableName = Schema::hasTable('receta') ? 'receta' : (Schema::hasTable('receta_version') ? 'receta_version' : null);

        if ($tableName === null || ! Schema::hasColumn($tableName, 'version')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('version');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('receta') ? 'receta' : (Schema::hasTable('receta_version') ? 'receta_version' : null);

        if ($tableName === null || Schema::hasColumn($tableName, 'version')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->unsignedInteger('version')->default(1)->after('ingredientes');
        });
    }
};
