<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep per paquete_id: the row with delivery_status, or the most recent if all null.
        // Compatible with MySQL and SQLite.
        $idsToDelete = DB::table('item_despacho as a')
            ->select('a.id')
            ->join('item_despacho as b', function ($join) {
                $join->on('a.paquete_id', '=', 'b.paquete_id')
                    ->whereColumn('a.id', '<>', 'b.id');
            })
            ->whereNotNull('a.paquete_id')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    // a has no status but b does → delete a
                    $q2->whereNull('a.delivery_status')
                        ->whereNotNull('b.delivery_status');
                })->orWhere(function ($q2) {
                    // same status (both null or equal) → keep the newer row, delete the older
                    $q2->where(function ($q3) {
                        $q3->whereNull('a.delivery_status')
                            ->whereNull('b.delivery_status');
                    })->orWhereColumn('a.delivery_status', '=', 'b.delivery_status');
                    $q2->whereColumn('a.created_at', '<', 'b.created_at');
                });
            })
            ->pluck('a.id');

        if ($idsToDelete->isNotEmpty()) {
            DB::table('item_despacho')->whereIn('id', $idsToDelete)->delete();
        }

        Schema::table('item_despacho', function (Blueprint $table) {
            $table->unique('paquete_id', 'item_despacho_paquete_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('item_despacho', function (Blueprint $table) {
            $table->dropUnique('item_despacho_paquete_id_unique');
        });
    }
};
