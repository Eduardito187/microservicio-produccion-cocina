<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration\Service;

use App\Application\Integration\Service\InboundPayloadValidator;
use Tests\TestCase;

/**
 * @class InboundPayloadValidatorTest
 */
class InboundPayloadValidatorTest extends TestCase
{
    private InboundPayloadValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new InboundPayloadValidator;
    }

    public function test_validate_enforces_required_fields_receta_actualizada(): void
    {
        $this->validator->validate('RecetaActualizada', [
            'id' => 'rec-1',
            'name' => 'Receta',
            'ingredients' => ['agua'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('payload missing required field: id|recetaId');

        $this->validator->validate('RecetaActualizada', [
            'name' => 'Receta',
            'ingredients' => ['agua'],
        ]);
    }

    public function test_validate_accepts_alternative_required_keys(): void
    {
        $this->validator->validate('paciente.paciente-creado', ['id' => 'pat-1']);
        $this->assertTrue(true);
    }

    public function test_validate_suscripcion_creada_happy_path(): void
    {
        $this->validator->validate('SuscripcionCreada', ['suscripcionId' => 'susc-1']);
        $this->assertTrue(true);
    }

    public function test_validate_suscripcion_creada_throws_when_id_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('payload missing required field: id|suscripcionId|contratoId');
        $this->validator->validate('SuscripcionCreada', ['nombre' => 'test']);
    }

    public function test_validate_suscripcion_actualizada(): void
    {
        $this->validator->validate('SuscripcionActualizada', ['id' => 'susc-2']);
        $this->assertTrue(true);
    }

    public function test_validate_suscripcion_crear_happy_path(): void
    {
        $this->validator->validate('suscripcion.crear', [
            'pacienteId' => 'p-1', 'tipoServicio' => 'classic', 'planId' => 'plan-1',
            'duracionDias' => 30, 'modalidadRevision' => 'auto', 'fechaInicio' => '2026-01-01',
        ]);
        $this->assertTrue(true);
    }

    public function test_validate_suscripcion_crear_throws_on_missing_field(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('payload missing required field: planId');
        $this->validator->validate('suscripcion.crear', [
            'pacienteId' => 'p-1', 'tipoServicio' => 'classic',
            'duracionDias' => 30, 'modalidadRevision' => 'auto', 'fechaInicio' => '2026-01-01',
        ]);
    }

    public function test_validate_receta_actualizada_throws_on_missing_name(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('payload missing required field: name|nombre');
        $this->validator->validate('RecetaActualizada', ['id' => 'rec-1', 'ingredients' => []]);
    }

    public function test_validate_receta_actualizada_throws_on_missing_ingredients(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('payload missing required field: ingredients|ingredientes');
        $this->validator->validate('RecetaActualizada', ['id' => 'rec-1', 'name' => 'Mi Receta']);
    }

    public function test_validate_planes_receta_creada(): void
    {
        $this->validator->validate('planes.receta-creada', [
            'recetaId' => 'r-42', 'nombre' => 'Arroz', 'ingredientes' => [['id' => 'i-1']],
        ]);
        $this->assertTrue(true);
    }

    public function test_validate_entrega_and_paquete_events(): void
    {
        foreach (['EntregaConfirmada', 'EntregaFallida', 'PaqueteEnRuta'] as $eventName) {
            $this->validator->validate($eventName, ['paqueteId' => 'pkg-1']);
        }
        $this->assertTrue(true);
    }

    public function test_validate_direccion_events(): void
    {
        foreach (['DireccionActualizada', 'DireccionGeocodificada', 'DireccionCreada'] as $eventName) {
            $this->validator->validate($eventName, ['direccionId' => 'dir-99']);
        }
        $this->assertTrue(true);
    }

    public function test_validate_paciente_events(): void
    {
        foreach (['PacienteActualizado', 'PacienteEliminado', 'PacienteCreado'] as $eventName) {
            $this->validator->validate($eventName, ['pacienteId' => 'p-test']);
        }
        $this->assertTrue(true);
    }

    public function test_validate_calendario_and_contrato_events(): void
    {
        $this->validator->validate('calendarios.sin-entrega', ['calendarioId' => 'cal-2']);
        $this->validator->validate('calendarios.direccion-entrega-cambiada', ['direccionId' => 'dir-3']);
        $this->validator->validate('contrato.generar', ['suscripcionId' => 's-1']);
        $this->validator->validate('contrato.consultar', ['contratoId' => 'cnt-2']);
        $this->validator->validate('contrato.cancelar', ['contratoId' => 'cnt-3']);
        $this->validator->validate('contrato.cancelado', ['contratoId' => 'cnt-4']);
        $this->validator->validate('EntregaProgramada', ['calendarioId' => 'cal-3', 'itemDespachoId' => 'id-1']);
        $this->validator->validate('DiaSinEntregaMarcado', ['calendarioId' => 'cal-4']);
        $this->validator->validate('DireccionEntregaCambiada', ['direccionId' => 'dir-5']);
        $this->validator->validate('calendario.servicio.generar', ['contratoId' => 'c-1', 'diasPermitidos' => 5, 'horarioPreferido' => '10:00']);
        $this->validator->validate('calendarios.crear-dia', ['calendarioId' => 'cal-1', 'fecha' => '2026-02-01']);
        $this->validator->validate('CalendarioEntregaCreado', ['calendarioId' => 'cal-5', 'fecha' => '2026-02-02']);
        $this->validator->validate('logistica.paquete.estado-actualizado', ['packageId' => 'pkg-1', 'deliveryStatus' => 'ok']);
        $this->assertTrue(true);
    }

    public function test_validate_paciente_direccion_events(): void
    {
        foreach ([
            'paciente.paciente-actualizado', 'paciente.paciente-eliminado',
            'paciente.direccion-creada', 'paciente.direccion-actualizada', 'paciente.direccion-geocodificada',
        ] as $eventName) {
            $key = str_contains($eventName, 'direccion') ? 'direccionId' : 'pacienteId';
            $this->validator->validate($eventName, [$key => 'id-1']);
        }
        $this->assertTrue(true);
    }

    public function test_validate_unknown_event_passes_without_requirements(): void
    {
        $this->validator->validate('evento.desconocido', ['anything' => 'ok']);
        $this->assertTrue(true);
    }
}
