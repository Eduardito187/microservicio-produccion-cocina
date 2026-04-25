<?php

/**
 * Microservicio "Produccion y Cocina"
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Restore ventanas incorrectly set to estado=0 — only expired ones (hasta < NOW()) should stay deactivated.
        DB::table('ventana_entrega')
            ->where('estado', 0)
            ->where('hasta', '>=', now())
            ->update(['estado' => 1]);
    }

    public function down(): void
    {
        // Not reversible — original estado values before the erroneous mass-deactivation are unknown.
    }
};
