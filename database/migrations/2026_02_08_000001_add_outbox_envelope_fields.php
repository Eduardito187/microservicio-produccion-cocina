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
     * @return void
     */
    public function up(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            $table->unsignedInteger('schema_version')->default(1)->after('aggregate_id');
            $table->uuid('correlation_id')->nullable()->after('schema_version')->index('outbox_correlation_id_index');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            $table->dropIndex('outbox_correlation_id_index');
            $table->dropColumn(['schema_version', 'correlation_id']);
        });
    }
};
