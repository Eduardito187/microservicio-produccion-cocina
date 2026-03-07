<?php
/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asegura que package_delivery_history.id tenga un DEFAULT (UUID()) a nivel
     * de motor MySQL, de modo que el campo nunca quede vacío aunque el código
     * olvidara proporcionarlo.
     *
     * Requiere MySQL 8.0+ (expresiones en DEFAULT).
     * No-op en otros motores (SQLite, etc.).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('package_delivery_history')) {
            return;
        }

        if (!Schema::hasColumn('package_delivery_history', 'id')) {
            DB::statement(
                "ALTER TABLE `package_delivery_history`
                 ADD COLUMN `id` CHAR(36) NOT NULL DEFAULT (UUID()) FIRST,
                 ADD PRIMARY KEY (`id`)"
            );
            return;
        }

        DB::statement(
            "ALTER TABLE `package_delivery_history`
             MODIFY COLUMN `id` CHAR(36) NOT NULL DEFAULT (UUID())"
        );
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (!Schema::hasTable('package_delivery_history')) {
            return;
        }

        if (!Schema::hasColumn('package_delivery_history', 'id')) {
            return;
        }

        DB::statement(
            "ALTER TABLE `package_delivery_history`
             MODIFY COLUMN `id` CHAR(36) NOT NULL"
        );
    }
};
