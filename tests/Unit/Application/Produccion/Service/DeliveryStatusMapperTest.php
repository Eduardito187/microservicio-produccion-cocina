<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Produccion\Service;

use App\Application\Produccion\Service\DeliveryStatusMapper;
use Tests\TestCase;

/**
 * @class DeliveryStatusMapperTest
 */
class DeliveryStatusMapperTest extends TestCase
{
    private DeliveryStatusMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new DeliveryStatusMapper;
    }

    public function test_map_status_aliases_de_confirmada(): void
    {
        foreach (['entregado', 'delivered', 'confirmada', 'confirmado', 'completed'] as $input) {
            [$status, $kpi] = $this->mapper->mapStatus($input);
            $this->assertSame('confirmada', $status->value(), "Fallo para input: {$input}");
            $this->assertSame('entrega_confirmada', $kpi);
        }
    }

    public function test_map_status_aliases_de_fallida(): void
    {
        foreach (['fallido', 'fallida', 'failed'] as $input) {
            [$status, $kpi] = $this->mapper->mapStatus($input);
            $this->assertSame('fallida', $status->value(), "Fallo para input: {$input}");
            $this->assertSame('entrega_fallida', $kpi);
        }
    }

    public function test_map_status_aliases_de_en_ruta(): void
    {
        foreach (['entransito', 'en_transito', 'en transito', 'intransit', 'onroute', 'en_ruta'] as $input) {
            [$status, $kpi] = $this->mapper->mapStatus($input);
            $this->assertSame('en_ruta', $status->value(), "Fallo para input: {$input}");
            $this->assertSame('paquete_en_ruta', $kpi);
        }
    }

    public function test_map_status_default_retorna_estado_actualizado_sin_kpi(): void
    {
        [$status, $kpi] = $this->mapper->mapStatus('desconocido');
        $this->assertSame('estado_actualizado', $status->value());
        $this->assertNull($kpi);
    }

    public function test_map_status_normaliza_mayusculas_y_espacios(): void
    {
        [$status] = $this->mapper->mapStatus('  DELIVERED  ');
        $this->assertSame('confirmada', $status->value());
    }

    public function test_parse_occurred_on_retorna_null_para_valores_vacios(): void
    {
        $this->assertNull($this->mapper->parseOccurredOn(null));
        $this->assertNull($this->mapper->parseOccurredOn(''));
        $this->assertNull($this->mapper->parseOccurredOn('   '));
    }

    public function test_parse_occurred_on_retorna_null_para_fecha_invalida(): void
    {
        $this->assertNull($this->mapper->parseOccurredOn('no-es-una-fecha'));
    }

    public function test_parse_occurred_on_parsea_fecha_iso8601_valida(): void
    {
        $result = $this->mapper->parseOccurredOn('2026-04-06T10:00:00+00:00');
        $this->assertNotNull($result);
    }

    public function test_parse_driver_id_retorna_null_para_valores_vacios(): void
    {
        $this->assertNull($this->mapper->parseDriverId(null));
        $this->assertNull($this->mapper->parseDriverId(''));
        $this->assertNull($this->mapper->parseDriverId('   '));
    }

    public function test_parse_driver_id_retorna_null_para_formato_invalido(): void
    {
        $this->assertNull($this->mapper->parseDriverId('no-es-uuid'));
    }

    public function test_parse_driver_id_parsea_uuid_valido(): void
    {
        $result = $this->mapper->parseDriverId('123e4567-e89b-12d3-a456-426614174000');
        $this->assertNotNull($result);
        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', $result->value());
    }

    public function test_parse_stored_status_retorna_null_para_valores_vacios(): void
    {
        $this->assertNull($this->mapper->parseStoredStatus(null));
        $this->assertNull($this->mapper->parseStoredStatus(''));
    }

    public function test_parse_stored_status_retorna_null_para_estado_invalido(): void
    {
        $this->assertNull($this->mapper->parseStoredStatus('estado_invalido_xyz'));
    }

    public function test_parse_stored_status_parsea_estado_valido(): void
    {
        $result = $this->mapper->parseStoredStatus('confirmada');
        $this->assertNotNull($result);
        $this->assertSame('confirmada', $result->value());
    }
}
