<?php

namespace Database\Seeders;

use Database\Seeders\DireccionesPacientesSeeder;
use Database\Seeders\DefaultSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DefaultSeeder::class,
            DireccionesPacientesSeeder::class
        ]);
    }
}