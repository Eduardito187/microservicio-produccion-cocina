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
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orden_produccion') && Schema::hasColumn('orden_produccion', 'sucursal_id')) {
            Schema::table('orden_produccion', function (Blueprint $table): void {
                $table->dropColumn('sucursal_id');
            });
        }

        if (Schema::hasTable('calendario') && Schema::hasColumn('calendario', 'sucursal_id')) {
            Schema::table('calendario', function (Blueprint $table): void {
                $table->dropUnique('calendario_fecha_sucursal_id_unique');
                $table->dropColumn('sucursal_id');
                $table->unique('fecha', 'calendario_fecha_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orden_produccion') && !Schema::hasColumn('orden_produccion', 'sucursal_id')) {
            Schema::table('orden_produccion', function (Blueprint $table): void {
                $table->string('sucursal_id')->nullable()->after('fecha');
            });
        }

        if (Schema::hasTable('calendario') && !Schema::hasColumn('calendario', 'sucursal_id')) {
            Schema::table('calendario', function (Blueprint $table): void {
                $table->dropUnique('calendario_fecha_unique');
                $table->string('sucursal_id')->nullable()->after('fecha');
                $table->unique(['fecha', 'sucursal_id'], 'calendario_fecha_sucursal_id_unique');
            });
        }
    }
};
