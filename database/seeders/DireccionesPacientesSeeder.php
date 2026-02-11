<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @class DireccionesPacientesSeeder
 * @package Database\Seeders
 */
class DireccionesPacientesSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now('America/La_Paz');
        $today = $now->copy()->startOfDay();

        // === SUSCRIPCION ===
        if (Schema::hasTable('suscripcion')) {
            DB::table('suscripcion')->upsert(
                [
                    ['id' => (string) Str::uuid(), 'nombre' => 'Mantener peso', 'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'nombre' => 'Masa muscular', 'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'nombre' => 'Bajar peso',    'created_at' => $now, 'updated_at' => $now],
                ],
                ['nombre'],          // clave única (asegúrate que en la migración suscripcion.nombre sea UNIQUE)
                ['updated_at']       // columnas a actualizar
            );

            // Mapa nombre → id para no hardcodear 1,2,3
            $suscripciones = DB::table('suscripcion')
                ->whereIn('nombre', ['Mantener peso', 'Masa muscular', 'Bajar peso'])
                ->pluck('id', 'nombre'); // ['Mantener peso' => 1, ...]
        } else {
            $suscripciones = collect();
        }

        // === PACIENTE ===
        if (Schema::hasTable('paciente')) {
            // Solo seguimos si tenemos ids de suscripciones
            if ($suscripciones->isNotEmpty()) {
                DB::table('paciente')->upsert(
                    [
                        [
                            'id'              => (string) Str::uuid(),
                            'nombre'          => 'Estelo',
                            'documento'       => 'CI-8178772',
                            'suscripcion_id'  => $suscripciones['Mantener peso'] ?? null,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ],
                        [
                            'id'              => (string) Str::uuid(),
                            'nombre'          => 'Pepe',
                            'documento'       => 'CI-8214882',
                            'suscripcion_id'  => $suscripciones['Masa muscular'] ?? null,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ],
                        [
                            'id'              => (string) Str::uuid(),
                            'nombre'          => 'Din',
                            'documento'       => 'CI-6358965',
                            'suscripcion_id'  => $suscripciones['Bajar peso'] ?? null,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ],
                    ],
                    ['documento'],       // asumiendo que documento es único (ponle UNIQUE en la migración)
                    ['nombre', 'suscripcion_id', 'updated_at']
                );
            }
        }

        // === DIRECCION ===
        if (Schema::hasTable('direccion')) {
            DB::table('direccion')->upsert(
                [
                    [
                        'id'          => (string) Str::uuid(),
                        'nombre'      => 'Casa Juan',
                        'linea1'      => 'Av. 16 de Julio 123',
                        'linea2'      => 'Depto 4B',
                        'ciudad'      => 'La Paz',
                        'provincia'   => 'La Paz',
                        'pais'        => 'Bolivia',
                        'geo'         => json_encode(['latitud' => -16.4990100, 'longitud' => -68.1462480]),
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                    [
                        'id'          => (string) Str::uuid(),
                        'nombre'      => 'Oficina María',
                        'linea1'      => 'Calle España 456',
                        'linea2'      => null,
                        'ciudad'      => 'Cochabamba',
                        'provincia'   => 'Cochabamba',
                        'pais'        => 'Bolivia',
                        'geo'         => json_encode(['latitud' => -16.4990100, 'longitud' => -68.1462480]),
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                    [
                        'id'          => (string) Str::uuid(),
                        'nombre'      => 'Casa Luis',
                        'linea1'      => 'Av. Cristo Redentor Km 6.5',
                        'linea2'      => 'Zona Norte',
                        'ciudad'      => 'Santa Cruz de la Sierra',
                        'provincia'   => 'Santa Cruz',
                        'pais'        => 'Bolivia',
                        'geo'         => json_encode(['latitud' => -16.4990100, 'longitud' => -68.1462480]),
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                ],
                ['nombre'],  // si decides que nombre de dirección debe ser único
                ['linea1', 'linea2', 'ciudad', 'provincia', 'pais', 'geo', 'updated_at']
            );
        }

        // === VENTANA_ENTREGA ===
        if (Schema::hasTable('ventana_entrega')) {
            DB::table('ventana_entrega')->insert([
                [
                    'id'         => (string) Str::uuid(),
                    'desde'      => $today->copy()->setTime(8, 0, 0),
                    'hasta'      => $today->copy()->setTime(12, 0, 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id'         => (string) Str::uuid(),
                    'desde'      => $today->copy()->setTime(13, 0, 0),
                    'hasta'      => $today->copy()->setTime(17, 0, 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id'         => (string) Str::uuid(),
                    'desde'      => $today->copy()->setTime(18, 0, 0),
                    'hasta'      => $today->copy()->setTime(21, 0, 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
            // Aquí no uso upsert porque probablemente no tienes una UNIQUE definida en (desde,hasta).
        }
    }
}
