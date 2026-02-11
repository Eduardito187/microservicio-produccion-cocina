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
        Schema::table('inbound_events', function (Blueprint $table) {
            $table->unsignedInteger('schema_version')->default(1)->after('event_id');
            $table->uuid('correlation_id')->nullable()->after('schema_version')->index('inbound_events_correlation_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbound_events', function (Blueprint $table) {
            $table->dropIndex('inbound_events_correlation_id_index');
            $table->dropColumn(['schema_version', 'correlation_id']);
        });
    }
};
