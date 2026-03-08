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
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        if (! Schema::hasTable('package_delivery_tracking')) {
            Schema::create('package_delivery_tracking', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('package_id')->unique('package_delivery_tracking_package_id_unique');
                $table->uuid('op_id')->nullable()->index('package_delivery_tracking_op_id_index');
                $table->string('entrega_id')->nullable();
                $table->string('contrato_id')->nullable();
                $table->uuid('driver_id')->nullable();
                $table->string('status', 40)->nullable();
                $table->boolean('status_locked')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->uuid('last_event_id')->nullable();
                $table->timestamp('last_occurred_on')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('package_delivery_history')) {
            Schema::create('package_delivery_history', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('event_id')->unique('package_delivery_history_event_id_unique');
                $table->uuid('package_id')->nullable()->index('package_delivery_history_package_id_index');
                $table->string('received_status', 40)->nullable();
                $table->uuid('driver_id')->nullable();
                $table->json('evidence')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('occurred_on')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('order_delivery_progress')) {
            Schema::create('order_delivery_progress', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('op_id')->unique('order_delivery_progress_op_id_unique');
                $table->unsignedInteger('total_packages')->default(0);
                $table->unsignedInteger('completed_packages')->default(0);
                $table->unsignedInteger('pending_packages')->default(0);
                $table->timestamp('all_completed_at')->nullable();
                $table->string('entrega_id')->nullable();
                $table->string('contrato_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_progress');
        Schema::dropIfExists('package_delivery_history');
        Schema::dropIfExists('package_delivery_tracking');
    }
};
