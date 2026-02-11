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
        Schema::create('entrega_evidencia', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id')->unique('entrega_evidencia_event_id_unique');
            $table->uuid('paquete_id')->nullable()->index('entrega_evidencia_paquete_id_index');
            $table->string('status', 40);
            $table->string('foto_url')->nullable();
            $table->json('geo')->nullable();
            $table->timestamp('occurred_on')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega_evidencia');
    }
};
