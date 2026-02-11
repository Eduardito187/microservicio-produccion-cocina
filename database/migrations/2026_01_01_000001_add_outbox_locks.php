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
        Schema::table('outbox', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->index('outbox_locked_at_index');
            $table->string('locked_by', 36)->nullable()->index('outbox_locked_by_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            $table->dropIndex('outbox_locked_at_index');
            $table->dropIndex('outbox_locked_by_index');
            $table->dropColumn(['locked_at', 'locked_by']);
        });
    }
};
