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
        Schema::create('orden_produccion', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->date('fecha');
            $t->string('sucursal_id');
            $t->string('estado');
            $t->timestamps();
        });

        Schema::create('produccion_batch', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('op_id'); $t->foreign('op_id')->references('id')->on('orden_produccion');
            $t->string('estacion_id');
            $t->string('receta_version_id');
            $t->string('porcion_id');
            $t->integer('cant_planificada');
            $t->integer('cant_producida')->default(0);
            $t->integer('merma_gr')->default(0);
            $t->string('estado');
            $t->timestamps();
        });

        Schema::create('lista_despacho', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('op_id')->unique();
            $t->date('fecha_entrega');
            $t->string('sucursal_id');
            $t->timestamps();
        });

        Schema::create('item_despacho', function (Blueprint $t) {
            $t->id();
            $t->string('lista_id'); $t->foreign('lista_id')->references('id')->on('lista_despacho');
            $t->string('etiqueta_id');
            $t->string('paciente_id');
            $t->json('direccion_snapshot');
            $t->json('ventana_entrega');
            $t->timestamps();
        });

        Schema::create('outbox', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('event_name');
            $t->string('aggregate_id');
            $t->json('payload');
            $t->timestamp('occurred_on');
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->index(['published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox');
        Schema::dropIfExists('item_despacho');
        Schema::dropIfExists('lista_despacho');
        Schema::dropIfExists('produccion_batch');
        Schema::dropIfExists('orden_produccion');
    }
};
