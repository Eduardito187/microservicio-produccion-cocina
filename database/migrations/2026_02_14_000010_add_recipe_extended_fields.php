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
        $tableName = Schema::hasTable('receta') ? 'receta' : 'receta_version';
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (!Schema::hasColumn($tableName, 'description')) {
                $table->text('description')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn($tableName, 'instructions')) {
                $table->text('instructions')->nullable()->after('description');
            }
            if (!Schema::hasColumn($tableName, 'total_calories')) {
                $table->unsignedInteger('total_calories')->nullable()->after('version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('receta') ? 'receta' : 'receta_version';
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            $columns = [];
            if (Schema::hasColumn($tableName, 'description')) {
                $columns[] = 'description';
            }
            if (Schema::hasColumn($tableName, 'instructions')) {
                $columns[] = 'instructions';
            }
            if (Schema::hasColumn($tableName, 'total_calories')) {
                $columns[] = 'total_calories';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
