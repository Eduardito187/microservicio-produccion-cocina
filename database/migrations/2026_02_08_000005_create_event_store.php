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
        Schema::create('event_store', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id')->unique('event_store_event_id_unique');
            $table->string('event_name', 150);
            $table->uuid('aggregate_id')->nullable()->index('event_store_aggregate_id_index');
            $table->json('payload');
            $table->timestamp('occurred_on');
            $table->unsignedInteger('schema_version')->default(1);
            $table->uuid('correlation_id')->nullable()->index('event_store_correlation_id_index');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};
