<?php
/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['order_item', 'produccion_batch', 'item_despacho'];

        foreach ($tables as $table) {
            $count = DB::table($table)->whereNull('op_id')->count();
            if ($count > 0) {
                throw new RuntimeException("Table {$table} has {$count} rows with NULL op_id.");
            }
        }

        DB::statement('ALTER TABLE order_item MODIFY op_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE produccion_batch MODIFY op_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE item_despacho MODIFY op_id CHAR(36) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE order_item MODIFY op_id CHAR(36) NULL');
        DB::statement('ALTER TABLE produccion_batch MODIFY op_id CHAR(36) NULL');
        DB::statement('ALTER TABLE item_despacho MODIFY op_id CHAR(36) NULL');
    }
};
