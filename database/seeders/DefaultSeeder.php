<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @class DefaultSeeder
 * @package Database\Seeders
 */
class DefaultSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        // === PRODUCTS ===
        if (Schema::hasTable('products')) {
            DB::table('products')->upsert(
                [
                    ['id' => (string) Str::uuid(), 'sku' => 'PIZZA-PEP',  'price' => 25,   'special_price' => 20, 'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'sku' => 'PIZZA-MARG', 'price' => 25,   'special_price' => 0,  'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'sku' => 'PIZZA-VEG',  'price' => 27.5, 'special_price' => 25, 'created_at' => $now, 'updated_at' => $now],
                ],
                ['sku'], // conflict key
                ['price', 'special_price', 'updated_at']
            );
        }

        // === RECETA ===
        $tablaReceta = Schema::hasTable('receta') ? 'receta' : (Schema::hasTable('receta_version') ? 'receta_version' : null);
        if ($tablaReceta !== null) {
            DB::table($tablaReceta)->upsert(
                [
                    [
                        'id' => (string) Str::uuid(),
                        'nombre'       => 'Pizza Margarita v1',
                        'description'  => 'Pizza clasica con tomate, mozzarella y albahaca.',
                        'instructions' => 'Preparar masa, agregar salsa e ingredientes y hornear.',
                        'nutrientes'   => json_encode([
                            'calorias'      => 800,
                            'proteinas'     => 30,
                            'grasas'        => 25,
                            'carbohidratos' => 100,
                        ]),
                        'ingredientes' => json_encode([
                            ['nombre' => 'Masa',      'cantidad' => '200g'],
                            ['nombre' => 'Tomate',    'cantidad' => '100g'],
                            ['nombre' => 'Mozzarella','cantidad' => '100g'],
                            ['nombre' => 'Albahaca',  'cantidad' => '5g'],
                        ]),
                        'total_calories' => 800,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'nombre'       => 'Pizza Pepperoni v1',
                        'description'  => 'Pizza con pepperoni y queso mozzarella.',
                        'instructions' => 'Preparar base, agregar salsa, queso y pepperoni, luego hornear.',
                        'nutrientes'   => json_encode([
                            'calorias'      => 950,
                            'proteinas'     => 40,
                            'grasas'        => 35,
                            'carbohidratos' => 110,
                        ]),
                        'ingredientes' => json_encode([
                            ['nombre' => 'Masa',      'cantidad' => '200g'],
                            ['nombre' => 'Tomate',    'cantidad' => '100g'],
                            ['nombre' => 'Mozzarella','cantidad' => '100g'],
                            ['nombre' => 'Pepperoni', 'cantidad' => '50g'],
                        ]),
                        'total_calories' => 950,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ],
                ],
                ['nombre'], // conflict key
                ['description', 'instructions', 'nutrientes', 'ingredientes', 'total_calories', 'updated_at']
            );
        }

        // === PORCION ===
        if (Schema::hasTable('porcion')) {
            DB::table('porcion')->upsert(
                [
                    ['id' => (string) Str::uuid(), 'nombre' => 'Individual', 'peso_gr' => 400,  'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'nombre' => 'Mediana',    'peso_gr' => 800,  'created_at' => $now, 'updated_at' => $now],
                    ['id' => (string) Str::uuid(), 'nombre' => 'Familiar',   'peso_gr' => 1200, 'created_at' => $now, 'updated_at' => $now],
                ],
                ['nombre'],
                ['peso_gr', 'updated_at']
            );
        }

    }
}
