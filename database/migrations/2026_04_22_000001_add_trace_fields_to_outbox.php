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
        Schema::table('outbox', function (Blueprint $table) {
            $table->string('trace_id', 32)->nullable()->after('correlation_id');
            $table->string('span_id', 16)->nullable()->after('trace_id');
            $table->index('trace_id', 'outbox_trace_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            $table->dropIndex('outbox_trace_id_index');
            $table->dropColumn(['trace_id', 'span_id']);
        });
    }
};
