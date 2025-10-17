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
        Schema::create('products', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('sku')->unique();
            $t->decimal('price', 18, 2);
            $t->decimal('special_price', 18, 2);
            $t->timestamps();
        });

        Schema::create('order_item', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('op_id');
            $t->foreign('op_id')->references('id')->on('orden_produccion');
            $t->string('p_id');
            $t->foreign('p_id')->references('id')->on('products');
            $t->integer('qty');
            $t->decimal('price', 18, 2);
            $t->decimal('final_price', 18, 2);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item');
        Schema::dropIfExists('products');
    }
};