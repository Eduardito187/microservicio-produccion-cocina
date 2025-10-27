<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DefaultSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // === PRODUCTS ===
        DB::table('products')->upsert([
            ['sku' => 'PIZZA-PEP', 'price' => 25, 'special_price' => 20, 'created_at' => $now],
            ['sku' => 'PIZZA-MARG', 'price' => 25, 'special_price' => 0, 'created_at' => $now],
            ['sku' => 'PIZZA-VEG', 'price' => 27.5, 'special_price' => 25, 'created_at' => $now],
        ], ['sku'], ['price', 'special_price', 'updated_at']);

        // === RECETA VERSION ===
        DB::table('receta_version')->upsert([
            [
                'nombre' => 'Pizza Margarita v1',
                'nutrientes' => json_encode([
                    'calorias' => 800,
                    'proteinas' => 30,
                    'grasas' => 25,
                    'carbohidratos' => 100
                ]),
                'ingredientes' => json_encode([
                    ['nombre' => 'Masa', 'cantidad' => '200g'],
                    ['nombre' => 'Tomate', 'cantidad' => '100g'],
                    ['nombre' => 'Mozzarella', 'cantidad' => '100g'],
                    ['nombre' => 'Albahaca', 'cantidad' => '5g']
                ]),
                'version' => 1,
                'created_at' => $now
            ],
            [
                'nombre' => 'Pizza Pepperoni v1',
                'nutrientes' => json_encode([
                    'calorias' => 950,
                    'proteinas' => 40,
                    'grasas' => 35,
                    'carbohidratos' => 110
                ]),
                'ingredientes' => json_encode([
                    ['nombre' => 'Masa', 'cantidad' => '200g'],
                    ['nombre' => 'Tomate', 'cantidad' => '100g'],
                    ['nombre' => 'Mozzarella', 'cantidad' => '100g'],
                    ['nombre' => 'Pepperoni', 'cantidad' => '50g']
                ]),
                'version' => 1,
                'created_at' => $now
            ],
        ], ['nombre'], ['nutrientes', 'ingredientes', 'version']);

        // === PORCION ===
        DB::table('porcion')->upsert([
            ['nombre' => 'Individual', 'peso_gr' => 400, 'created_at' => $now],
            ['nombre' => 'Mediana', 'peso_gr' => 800, 'created_at' => $now],
            ['nombre' => 'Familiar', 'peso_gr' => 1200, 'created_at' => $now]
        ], ['nombre'], ['peso_gr']);

        // === ESTACION ===
        DB::table('estacion')->upsert([
            ['nombre' => 'Preparación de masa', 'capacidad' => 2, 'created_at' => $now],
            ['nombre' => 'Salsa y toppings', 'capacidad' => 2, 'created_at' => $now],
            ['nombre' => 'Horno', 'capacidad' => 1, 'created_at' => $now],
            ['nombre' => 'Empaque', 'capacidad' => 2, 'created_at' => $now]
        ], ['nombre'], ['capacidad']);
    }
}