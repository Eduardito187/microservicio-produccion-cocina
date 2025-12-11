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
        if (!Schema::hasTable('calendario')) {
            Schema::create('calendario', function (Blueprint $table) {
                $table->id();
                $table->date('fecha');
                $table->string('sucursal_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['fecha', 'sucursal_id'], 'calendario_fecha_sucursal_id_unique');
            });
        }

        if (!Schema::hasTable('direccion')) {
            Schema::create('direccion', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->nullable();
                $table->string('linea1');
                $table->string('linea2')->nullable();
                $table->string('ciudad')->nullable();
                $table->string('provincia')->nullable();
                $table->string('pais')->nullable();
                $table->json('geo')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('estacion')) {
            Schema::create('estacion', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->unsignedInteger('capacidad')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('inbound_events')) {
            Schema::create('inbound_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 100);
                $table->string('event_name', 150);
                $table->string('occurred_on')->nullable();
                $table->longText('payload');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique('event_id', 'inbound_events_event_id_unique');
            });
        }

        if (!Schema::hasTable('orden_produccion')) {
            Schema::create('orden_produccion', function (Blueprint $table) {
                $table->id();
                $table->date('fecha');
                $table->string('sucursal_id');
                $table->string('estado');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('outbox')) {
            Schema::create('outbox', function (Blueprint $table) {
                $table->id();
                $table->string('event_name');
                $table->string('aggregate_id');
                $table->json('payload');
                $table->timestamp('occurred_on');
                $table->timestamp('published_at')->nullable()->index('outbox_published_at_index');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('porcion')) {
            Schema::create('porcion', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->unsignedInteger('peso_gr');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('sku')->unique();
                $table->decimal('price', 10, 2);
                $table->decimal('special_price', 10, 2)->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('receta_version')) {
            Schema::create('receta_version', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->json('nutrientes')->nullable();
                $table->json('ingredientes')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('suscripcion')) {
            Schema::create('suscripcion', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('ventana_entrega')) {
            Schema::create('ventana_entrega', function (Blueprint $table) {
                $table->id();
                $table->dateTime('desde');
                $table->dateTime('hasta');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('paciente')) {
            Schema::create('paciente', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->string('documento')->nullable();
                $table->unsignedBigInteger('suscripcion_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->foreign('suscripcion_id', 'paciente_suscripcion_id_foreign')->references('id')->on('suscripcion')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('order_item')) {
            Schema::create('order_item', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('op_id')->nullable();
                $table->unsignedBigInteger('p_id')->nullable();
                $table->integer('qty');
                $table->decimal('price', 18, 2);
                $table->decimal('final_price', 18, 2);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index('op_id', 'order_item_op_id_foreign');
                $table->index('p_id', 'order_item_p_id_foreign');

                $table->foreign('op_id', 'order_item_op_id_foreign')->references('id')->on('orden_produccion')->cascadeOnDelete();
                $table->foreign('p_id', 'order_item_p_id_foreign')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('produccion_batch')) {
            Schema::create('produccion_batch', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('op_id')->nullable();
                $table->unsignedBigInteger('p_id')->nullable();
                $table->integer('cant_planificada');
                $table->integer('cant_producida')->default(0);
                $table->integer('merma_gr')->default(0);
                $table->decimal('rendimiento', 18, 2)->nullable();
                $table->string('estado');
                $table->integer('qty');
                $table->integer('posicion')->default(0);
                $table->json('ruta')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('estacion_id')->nullable();
                $table->unsignedBigInteger('receta_version_id')->nullable();
                $table->unsignedBigInteger('porcion_id')->nullable();

                $table->index('p_id', 'produccion_batch_p_id_foreign');
                $table->index('op_id', 'idx_pb_op');
                $table->index(['op_id', 'p_id'], 'idx_pb_op_p');
                $table->index(['op_id', 'posicion'], 'idx_pb_op_pos');
                $table->index('porcion_id', 'produccion_batch_porcion_id_foreign');
                $table->index(['receta_version_id', 'porcion_id'], 'idx_pb_rec_por');
                $table->index('estacion_id', 'idx_pb_est');

                $table->foreign('estacion_id', 'produccion_batch_estacion_id_foreign')->references('id')->on('estacion');
                $table->foreign('op_id', 'produccion_batch_op_id_foreign')->references('id')->on('orden_produccion')->cascadeOnDelete();
                $table->foreign('p_id', 'produccion_batch_p_id_foreign')->references('id')->on('products')->cascadeOnDelete();
                $table->foreign('porcion_id', 'produccion_batch_porcion_id_foreign')->references('id')->on('porcion');
                $table->foreign('receta_version_id', 'produccion_batch_receta_version_id_foreign')->references('id')->on('receta_version');
            });
        }

        if (!Schema::hasTable('etiqueta')) {
            Schema::create('etiqueta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('receta_version_id')->nullable();
                $table->unsignedBigInteger('suscripcion_id')->nullable();
                $table->unsignedBigInteger('paciente_id')->nullable();
                $table->json('qr_payload')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(
                    ['receta_version_id', 'paciente_id'],
                    'uk_etq_compuesta'
                );

                $table->index('receta_version_id', 'etiqueta_receta_version_id_foreign');
                $table->index('suscripcion_id', 'etiqueta_suscripcion_id_foreign');
                $table->index('paciente_id', 'etiqueta_paciente_id_foreign');

                $table->foreign('paciente_id', 'etiqueta_paciente_id_foreign')->references('id')->on('paciente')->nullOnDelete();
                $table->foreign('receta_version_id', 'etiqueta_receta_version_id_foreign')->references('id')->on('receta_version')->nullOnDelete();
                $table->foreign('suscripcion_id', 'etiqueta_suscripcion_id_foreign')->references('id')->on('suscripcion')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('paquete')) {
            Schema::create('paquete', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('etiqueta_id')->nullable();
                $table->unsignedBigInteger('ventana_id')->nullable();
                $table->unsignedBigInteger('direccion_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index('etiqueta_id', 'paquete_etiqueta_id_foreign');
                $table->index('ventana_id', 'paquete_ventana_id_foreign');
                $table->index('direccion_id', 'paquete_direccion_id_foreign');

                $table->foreign('direccion_id', 'paquete_direccion_id_foreign')->references('id')->on('direccion')->cascadeOnDelete();
                $table->foreign('etiqueta_id', 'paquete_etiqueta_id_foreign')->references('id')->on('etiqueta')->cascadeOnDelete();
                $table->foreign('ventana_id', 'paquete_ventana_id_foreign')->references('id')->on('ventana_entrega')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('item_despacho')) {
            Schema::create('item_despacho', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('op_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('paquete_id')->nullable();

                $table->index('op_id', 'item_despacho_op_id_foreign');
                $table->index('product_id', 'item_despacho_product_id_foreign');
                $table->index('paquete_id', 'item_despacho_paquete_id_foreign');

                $table->foreign('op_id', 'item_despacho_op_id_foreign')->references('id')->on('orden_produccion')->cascadeOnDelete();
                $table->foreign('paquete_id', 'item_despacho_paquete_id_foreign')->references('id')->on('paquete')->cascadeOnDelete();
                $table->foreign('product_id', 'item_despacho_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('calendario_item')) {
            Schema::create('calendario_item', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('calendario_id')->nullable();
                $table->unsignedBigInteger('item_despacho_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique(['calendario_id', 'item_despacho_id'], 'calendario_item_calendario_id_item_despacho_id_unique');
                $table->index('item_despacho_id', 'calendario_item_item_despacho_id_foreign');

                $table->foreign('calendario_id', 'calendario_item_calendario_id_foreign')->references('id')->on('calendario')->cascadeOnDelete();
                $table->foreign('item_despacho_id', 'calendario_item_item_despacho_id_foreign')->references('id')->on('item_despacho')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_item');
        Schema::dropIfExists('item_despacho');
        Schema::dropIfExists('paquete');
        Schema::dropIfExists('etiqueta');
        Schema::dropIfExists('produccion_batch');
        Schema::dropIfExists('order_item');
        Schema::dropIfExists('paciente');

        Schema::dropIfExists('ventana_entrega');
        Schema::dropIfExists('suscripcion');
        Schema::dropIfExists('receta_version');
        Schema::dropIfExists('products');
        Schema::dropIfExists('porcion');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('outbox');
        Schema::dropIfExists('orden_produccion');
        Schema::dropIfExists('inbound_events');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('estacion');
        Schema::dropIfExists('direccion');
        Schema::dropIfExists('calendario');
    }
};
