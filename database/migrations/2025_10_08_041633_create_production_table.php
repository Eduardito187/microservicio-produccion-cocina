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
            $t->bigIncrements('id');
            $t->date('fecha');
            $t->string('sucursal_id');
            $t->string('estado');
            $t->timestamps();
        });

        Schema::create('produccion_batch', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('op_id')->nullable();
            $t->foreign('op_id')->references('id')->on('orden_produccion')->onDelete('cascade');
            $t->integer('cant_planificada');
            $t->integer('cant_producida')->default(0);
            $t->integer('merma_gr')->default(0);
            $t->string('estado');
            $t->timestamps();
        });

        Schema::create('lista_despacho', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('op_id')->unique();
            $t->date('fecha_entrega');
            $t->string('sucursal_id');
            $t->timestamps();
        });

        Schema::create('item_despacho', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('lista_id')->nullable();
            $t->foreign('lista_id')->references('id')->on('lista_despacho')->onDelete('cascade');
            $t->string('sku');
            $t->integer('etiqueta_id');
            $t->json('direccion_snapshot');
            $t->json('ventana_entrega');
            $t->timestamps();
        });

        Schema::create('outbox', function (Blueprint $t) {
            $t->bigIncrements('id');
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
