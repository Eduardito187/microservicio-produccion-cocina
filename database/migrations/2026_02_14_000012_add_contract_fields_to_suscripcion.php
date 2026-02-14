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
        Schema::table('suscripcion', function (Blueprint $table): void {
            if (!Schema::hasColumn('suscripcion', 'paciente_id')) {
                $table->uuid('paciente_id')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('suscripcion', 'tipo_servicio')) {
                $table->string('tipo_servicio')->nullable()->after('paciente_id');
            }
            if (!Schema::hasColumn('suscripcion', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('tipo_servicio');
            }
            if (!Schema::hasColumn('suscripcion', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }
            if (!Schema::hasColumn('suscripcion', 'estado')) {
                $table->string('estado', 30)->default('ACTIVA')->after('fecha_fin');
            }
            if (!Schema::hasColumn('suscripcion', 'motivo_cancelacion')) {
                $table->text('motivo_cancelacion')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('suscripcion', 'cancelado_at')) {
                $table->timestamp('cancelado_at')->nullable()->after('motivo_cancelacion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suscripcion', function (Blueprint $table): void {
            $columns = [];
            foreach (['paciente_id', 'tipo_servicio', 'fecha_inicio', 'fecha_fin', 'estado', 'motivo_cancelacion', 'cancelado_at'] as $column) {
                if (Schema::hasColumn('suscripcion', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
