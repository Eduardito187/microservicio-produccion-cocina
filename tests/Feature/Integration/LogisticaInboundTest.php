<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Application\Integration\Handlers\EntregaConfirmadaHandler;
use App\Application\Integration\Handlers\EntregaFallidaHandler;
use App\Application\Integration\Handlers\PaqueteEnRutaHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @class LogisticaInboundTest
 * @package Tests\Feature\Integration
 */
class LogisticaInboundTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_entrega_confirmada_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(EntregaConfirmadaHandler::class);

        $payload = [
            'paqueteId' => 'paq-10',
            'fotoUrl' => 'http://example.com/foto.jpg',
            'geo' => ['lat' => '1.0', 'lng' => '2.0'],
            'occurredOn' => '2025-10-10T10:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-10']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-10',
            'paquete_id' => 'paq-10',
            'status' => 'confirmada',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'entrega_confirmada',
            'value' => 1,
        ]);
    }

    /**
     * @return void
     */
    public function test_entrega_fallida_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(EntregaFallidaHandler::class);

        $payload = [
            'paqueteId' => 'paq-20',
            'motivo' => 'no atencion',
            'occurredOn' => '2025-10-10T11:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-20']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-20',
            'paquete_id' => 'paq-20',
            'status' => 'fallida',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'entrega_fallida',
            'value' => 1,
        ]);
    }

    /**
     * @return void
     */
    public function test_paquete_en_ruta_guarda_evidencia_y_kpi(): void
    {
        $handler = $this->app->make(PaqueteEnRutaHandler::class);

        $payload = [
            'paqueteId' => 'paq-30',
            'rutaId' => 'ruta-1',
            'occurredOn' => '2025-10-10T12:00:00Z',
        ];

        $handler->handle($payload, ['event_id' => 'evt-30']);

        $this->assertDatabaseHas('entrega_evidencia', [
            'event_id' => 'evt-30',
            'paquete_id' => 'paq-30',
            'status' => 'en_ruta',
        ]);

        $this->assertDatabaseHas('kpi_operativo', [
            'name' => 'paquete_en_ruta',
            'value' => 1,
        ]);
    }
}
