<?php

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
        Schema::table('produccion_batch', function (Blueprint $t) {
            $t->string('sku')->after('op_id');
            $t->integer('qty')->after('sku');
            $t->integer('posicion')->default(0)->after('estacion_id');
            $t->json('ruta')->nullable()->after('posicion');
            $t->index(['op_id'], 'idx_pb_op');
            $t->index(['estacion_id'], 'idx_pb_est');
            $t->index(['op_id', 'posicion'], 'idx_pb_op_pos');
            $t->index(['sku'], 'idx_pb_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produccion_batch', function (Blueprint $table) {
            $table->dropIndex('idx_pb_op');
            $table->dropIndex('idx_pb_est');
            $table->dropIndex('idx_pb_op_pos');
            $table->dropIndex('idx_pb_sku');
        });
    }
};