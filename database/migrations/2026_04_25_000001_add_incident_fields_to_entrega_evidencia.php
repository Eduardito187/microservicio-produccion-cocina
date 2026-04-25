<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrega_evidencia', function (Blueprint $table) {
            $table->string('driver_id', 36)->nullable()->after('paquete_id');
            $table->string('incident_type', 80)->nullable()->after('foto_url');
            $table->text('incident_description')->nullable()->after('incident_type');
        });
    }

    public function down(): void
    {
        Schema::table('entrega_evidencia', function (Blueprint $table) {
            $table->dropColumn(['driver_id', 'incident_type', 'incident_description']);
        });
    }
};
