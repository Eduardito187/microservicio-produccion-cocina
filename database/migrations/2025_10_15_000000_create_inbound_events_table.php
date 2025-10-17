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
        Schema::create('inbound_events', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('event_id', 100)->unique();
            $t->string('event_name', 150);
            $t->string('occurred_on')->nullable();
            $t->longText('payload');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_events');
    }
};