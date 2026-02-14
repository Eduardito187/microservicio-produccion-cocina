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
        Schema::table('receta_version', function (Blueprint $table): void {
            if (!Schema::hasColumn('receta_version', 'description')) {
                $table->text('description')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('receta_version', 'instructions')) {
                $table->text('instructions')->nullable()->after('description');
            }
            if (!Schema::hasColumn('receta_version', 'total_calories')) {
                $table->unsignedInteger('total_calories')->nullable()->after('version');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receta_version', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('receta_version', 'description')) {
                $columns[] = 'description';
            }
            if (Schema::hasColumn('receta_version', 'instructions')) {
                $columns[] = 'instructions';
            }
            if (Schema::hasColumn('receta_version', 'total_calories')) {
                $columns[] = 'total_calories';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
