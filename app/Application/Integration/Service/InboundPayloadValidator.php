<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Service;

/**
 * Validates that a decoded inbound event payload contains all required fields.
 * Throws \RuntimeException when validation fails (non-retryable by the consumer).
 *
 * @class InboundPayloadValidator
 */
class InboundPayloadValidator
{
    /**
     * @throws \RuntimeException
     */
    public function validate(string $eventName, array $payload): void
    {
        if (
            $eventName === 'RecetaActualizada'
            || $eventName === 'planes.receta-creada'
            || $eventName === 'planes.receta-actualizada'
        ) {
            $hasId = (isset($payload['id']) && $payload['id'] !== '')
                || (isset($payload['recetaId']) && $payload['recetaId'] !== '');
            if (! $hasId) {
                throw new \RuntimeException('payload missing required field: id|recetaId');
            }
            if (! array_key_exists('name', $payload) && ! array_key_exists('nombre', $payload)) {
                throw new \RuntimeException('payload missing required field: name|nombre');
            }
            if (! array_key_exists('ingredients', $payload) && ! array_key_exists('ingredientes', $payload)) {
                throw new \RuntimeException('payload missing required field: ingredients|ingredientes');
            }

            return;
        }

        if ($eventName === 'SuscripcionCreada' || $eventName === 'SuscripcionActualizada') {
            $hasId = (isset($payload['id']) && $payload['id'] !== '')
                || (isset($payload['suscripcionId']) && $payload['suscripcionId'] !== '')
                || (isset($payload['contratoId']) && $payload['contratoId'] !== '');
            if (! $hasId) {
                throw new \RuntimeException('payload missing required field: id|suscripcionId|contratoId');
            }

            return;
        }

        if ($eventName === 'suscripcion.crear') {
            $required = ['pacienteId', 'tipoServicio', 'planId', 'duracionDias', 'modalidadRevision', 'fechaInicio'];
            foreach ($required as $key) {
                if (! array_key_exists($key, $payload) || $payload[$key] === null || $payload[$key] === '') {
                    throw new \RuntimeException("payload missing required field: {$key}");
                }
            }

            return;
        }

        $requirements = [
            'DireccionCreada' => ['direccionId'],
            'DireccionActualizada' => ['direccionId'],
            'DireccionGeocodificada' => ['direccionId'],
            'PacienteCreado' => ['pacienteId'],
            'PacienteActualizado' => ['pacienteId'],
            'PacienteEliminado' => ['pacienteId'],
            'SuscripcionCreada' => ['suscripcionId'],
            'SuscripcionActualizada' => ['suscripcionId'],
            'contrato.creado' => ['contratoId', 'tipoServicio'],
            'contrato.generar' => ['suscripcionId'],
            'contrato.consultar' => ['contratoId'],
            'contrato.cancelar' => ['contratoId'],
            'contrato.cancelado' => ['contratoId'],
            'calendario.servicio.generar' => ['contratoId', 'diasPermitidos', 'horarioPreferido'],
            'calendario.generado' => ['contratoId', 'listaFechasEntrega'],
            'CalendarioEntregaCreado' => ['calendarioId|id|entregaId|suscripcionId', 'fecha|date|occurredOn|occurred_on'],
            'EntregaProgramada' => ['calendarioId', 'itemDespachoId'],
            'DiaSinEntregaMarcado' => ['calendarioId'],
            'DireccionEntregaCambiada' => ['direccionId'],
            'calendarios.crear-dia' => ['calendarioId|id|entregaId|suscripcionId', 'fecha|date|occurredOn|occurred_on'],
            'calendarios.sin-entrega' => ['calendarioId'],
            'calendarios.direccion-entrega-cambiada' => ['direccionId'],
            'EntregaConfirmada' => ['paqueteId'],
            'EntregaFallida' => ['paqueteId'],
            'PaqueteEnRuta' => ['paqueteId'],
            'logistica.paquete.estado-actualizado' => ['packageId', 'deliveryStatus'],
            'paciente.paciente-creado' => ['pacienteId|id|paciente_id'],
            'paciente.paciente-actualizado' => ['pacienteId|id|paciente_id'],
            'paciente.paciente-eliminado' => ['pacienteId|id|paciente_id'],
            'paciente.direccion-creada' => ['direccionId|id|direccion_id'],
            'paciente.direccion-actualizada' => ['direccionId|id|direccion_id'],
            'paciente.direccion-geocodificada' => ['direccionId|id|direccion_id'],
        ];

        $required = $requirements[$eventName] ?? [];
        foreach ($required as $key) {
            $alternatives = array_map('trim', explode('|', $key));
            $found = false;
            foreach ($alternatives as $candidate) {
                if (array_key_exists($candidate, $payload) && $payload[$candidate] !== null && $payload[$candidate] !== '') {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                throw new \RuntimeException("payload missing required field: {$key}");
            }
        }
    }
}
