<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DireccionesPacientesSeeder extends Seeder
{
    public function run(): void
    {
        // Use a consistent "now" in La Paz timezone
        $now = Carbon::now('America/La_Paz');

        // === PACIENTE ===
        $pacienteJuanId = DB::table('paciente')->insertGetId([
            'nombre'      => 'Juan Pérez',
            'documento'   => 'CI-1234567',
            'created_at'  => $now,
            'updated_at'  => null,
        ]);

        $pacienteMariaId = DB::table('paciente')->insertGetId([
            'nombre'      => 'María Gómez',
            'documento'   => 'CI-7654321',
            'created_at'  => $now,
            'updated_at'  => null,
        ]);

        $pacienteLuisId = DB::table('paciente')->insertGetId([
            'nombre'      => 'Luis Andrade',
            'documento'   => null, // unknown
            'created_at'  => $now,
            'updated_at'  => null,
        ]);

        // === SUSCRIPCION ===
        DB::table('suscripcion')->insert([
            [
                'paciente_id' => $pacienteJuanId,
                'estado'      => 'activa',
                'created_at'  => $now,
                'updated_at'  => null,
            ],
            [
                'paciente_id' => $pacienteMariaId,
                'estado'      => 'pausada',
                'created_at'  => $now,
                'updated_at'  => null,
            ],
            [
                'paciente_id' => $pacienteLuisId,
                'estado'      => 'cancelada',
                'created_at'  => $now,
                'updated_at'  => null,
            ],
        ]);

        // === DIRECCION ===
        DB::table('direccion')->insert([
            [
                'nombre'     => 'Casa Juan',
                'linea1'     => 'Av. 16 de Julio 123',
                'linea2'     => 'Depto 4B',
                'ciudad'     => 'La Paz',
                'provincia'  => 'La Paz',
                'pais'       => 'Bolivia',
                'latitud'    => -16.4990100,
                'longitud'   => -68.1462480,
                'created_at' => $now,
                'updated_at' => null,
            ],
            [
                'nombre'     => 'Oficina María',
                'linea1'     => 'Calle España 456',
                'linea2'     => null,
                'ciudad'     => 'Cochabamba',
                'provincia'  => 'Cochabamba',
                'pais'       => 'Bolivia',
                'latitud'    => -17.3936000,
                'longitud'   => -66.1570000,
                'created_at' => $now,
                'updated_at' => null,
            ],
            [
                'nombre'     => 'Casa Luis',
                'linea1'     => 'Av. Cristo Redentor Km 6.5',
                'linea2'     => 'Zona Norte',
                'ciudad'     => 'Santa Cruz de la Sierra',
                'provincia'  => 'Santa Cruz',
                'pais'       => 'Bolivia',
                'latitud'    => -17.7333000,
                'longitud'   => -63.1667000,
                'created_at' => $now,
                'updated_at' => null,
            ],
        ]);

        // === VENTANA_ENTREGA ===
        // Three practical delivery windows: morning, afternoon, evening
        $today = $now->copy()->startOfDay();

        DB::table('ventana_entrega')->insert([
            [
                'desde'      => $today->copy()->setTime(8, 0, 0),  // 08:00
                'hasta'      => $today->copy()->setTime(12, 0, 0), // 12:00
                'created_at' => $now,
                'updated_at' => null,
            ],
            [
                'desde'      => $today->copy()->setTime(13, 0, 0), // 13:00
                'hasta'      => $today->copy()->setTime(17, 0, 0), // 17:00
                'created_at' => $now,
                'updated_at' => null,
            ],
            [
                'desde'      => $today->copy()->setTime(18, 0, 0), // 18:00
                'hasta'      => $today->copy()->setTime(21, 0, 0), // 21:00
                'created_at' => $now,
                'updated_at' => null,
            ],
        ]);
    }
}