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
        if (Schema::hasTable('order_delivery_progress')) {
            Schema::table('order_delivery_progress', function (Blueprint $table) {
                if (!Schema::hasColumn('order_delivery_progress', 'completion_event_id')) {
                    $table->uuid('completion_event_id')->nullable()->after('all_completed_at');
                    $table->index('completion_event_id', 'order_delivery_progress_completion_event_id_index');
                }
            });
        }

        if (!Schema::hasTable('delivery_inconsistency_queue')) {
            Schema::create('delivery_inconsistency_queue', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('event_id')->nullable()->index('delivery_inconsistency_event_id_index');
                $table->uuid('package_id')->nullable()->index('delivery_inconsistency_package_id_index');
                $table->uuid('op_id')->nullable()->index('delivery_inconsistency_op_id_index');
                $table->string('reason', 120);
                $table->json('payload')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_delivery_progress')) {
            Schema::table('order_delivery_progress', function (Blueprint $table) {
                if (Schema::hasColumn('order_delivery_progress', 'completion_event_id')) {
                    $table->dropIndex('order_delivery_progress_completion_event_id_index');
                    $table->dropColumn('completion_event_id');
                }
            });
        }

        Schema::dropIfExists('delivery_inconsistency_queue');
    }
};
