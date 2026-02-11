<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Database\Seeders;

use Database\Seeders\DireccionesPacientesSeeder;
use Database\Seeders\DefaultSeeder;
use Illuminate\Database\Seeder;

/**
 * @class DatabaseSeeder
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $this->call([
            DefaultSeeder::class,
            DireccionesPacientesSeeder::class
        ]);
    }
}
