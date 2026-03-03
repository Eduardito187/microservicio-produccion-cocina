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
        if (Schema::hasTable('delivery_inconsistency_queue')) {
            Schema::table('delivery_inconsistency_queue', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_inconsistency_queue', 'reason')) {
                    return;
                }

                $table->index('reason', 'delivery_inconsistency_reason_index');
                $table->index('created_at', 'delivery_inconsistency_created_at_index');
                $table->index(['reason', 'created_at'], 'delivery_inconsistency_reason_created_at_index');
            });
        }

        if (Schema::hasTable('package_delivery_history')) {
            Schema::table('package_delivery_history', function (Blueprint $table) {
                $table->index('driver_id', 'package_delivery_history_driver_id_index');
                $table->index('occurred_on', 'package_delivery_history_occurred_on_index');
            });
        }

        if (Schema::hasTable('package_delivery_tracking')) {
            Schema::table('package_delivery_tracking', function (Blueprint $table) {
                $table->index('driver_id', 'package_delivery_tracking_driver_id_index');
                $table->index('status', 'package_delivery_tracking_status_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('package_delivery_tracking')) {
            Schema::table('package_delivery_tracking', function (Blueprint $table) {
                $table->dropIndex('package_delivery_tracking_status_index');
                $table->dropIndex('package_delivery_tracking_driver_id_index');
            });
        }

        if (Schema::hasTable('package_delivery_history')) {
            Schema::table('package_delivery_history', function (Blueprint $table) {
                $table->dropIndex('package_delivery_history_occurred_on_index');
                $table->dropIndex('package_delivery_history_driver_id_index');
            });
        }

        if (Schema::hasTable('delivery_inconsistency_queue')) {
            Schema::table('delivery_inconsistency_queue', function (Blueprint $table) {
                $table->dropIndex('delivery_inconsistency_reason_created_at_index');
                $table->dropIndex('delivery_inconsistency_created_at_index');
                $table->dropIndex('delivery_inconsistency_reason_index');
            });
        }
    }
};
