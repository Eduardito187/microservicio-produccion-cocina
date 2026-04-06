<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Domain\Produccion;

use App\Domain\Produccion\Events\CalendarioCreado;
use App\Domain\Produccion\Events\OrdenEntregaCompletada;
use App\Domain\Produccion\Events\PaqueteEntregado;
use App\Domain\Produccion\Policy\PackageDeliveryTransitionPolicy;
use App\Domain\Produccion\ValueObjects\ContratoId;
use App\Domain\Produccion\ValueObjects\DriverId;
use App\Domain\Produccion\ValueObjects\EntregaId;
use App\Domain\Produccion\ValueObjects\OccurredOn;
use App\Domain\Produccion\ValueObjects\PackageStatus;
use App\Domain\Shared\ValueObjects\ValueObject;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @class DomainValueObjectsTest
 */
class DomainValueObjectsTest extends TestCase
{
    // ─── PackageStatus ────────────────────────────────────────────────────

    public function test_package_status_acepta_valores_validos(): void
    {
        foreach (['pendiente', 'en_ruta', 'fallida', 'estado_actualizado', 'confirmada'] as $status) {
            $vo = new PackageStatus($status);
            $this->assertSame($status, $vo->value());
        }
    }

    public function test_package_status_normaliza_a_minusculas_y_sin_espacios(): void
    {
        $vo = new PackageStatus('  EN_RUTA  ');
        $this->assertSame('en_ruta', $vo->value());
    }

    public function test_package_status_lanza_excepcion_si_vacio(): void
    {
        $this->expectException(DomainException::class);
        new PackageStatus('');
    }

    public function test_package_status_lanza_excepcion_si_solo_espacios(): void
    {
        $this->expectException(DomainException::class);
        new PackageStatus('   ');
    }

    public function test_package_status_lanza_excepcion_si_valor_invalido(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Invalid PackageStatus/');
        new PackageStatus('desconocido');
    }

    public function test_package_status_is_completed_es_true_solo_para_confirmada(): void
    {
        $this->assertTrue((new PackageStatus('confirmada'))->isCompleted());
        $this->assertFalse((new PackageStatus('pendiente'))->isCompleted());
        $this->assertFalse((new PackageStatus('en_ruta'))->isCompleted());
        $this->assertFalse((new PackageStatus('fallida'))->isCompleted());
        $this->assertFalse((new PackageStatus('estado_actualizado'))->isCompleted());
    }

    public function test_package_status_puede_transicionar_desde_no_completado_a_cualquiera(): void
    {
        $current = new PackageStatus('pendiente');
        $this->assertTrue($current->canTransitionTo(new PackageStatus('en_ruta')));
        $this->assertTrue($current->canTransitionTo(new PackageStatus('confirmada')));
        $this->assertTrue($current->canTransitionTo(new PackageStatus('fallida')));
    }

    public function test_package_status_confirmada_solo_puede_transicionar_a_confirmada(): void
    {
        $current = new PackageStatus('confirmada');
        $this->assertTrue($current->canTransitionTo(new PackageStatus('confirmada')));
        $this->assertFalse($current->canTransitionTo(new PackageStatus('en_ruta')));
        $this->assertFalse($current->canTransitionTo(new PackageStatus('fallida')));
        $this->assertFalse($current->canTransitionTo(new PackageStatus('pendiente')));
    }

    // ─── OccurredOn ───────────────────────────────────────────────────────

    public function test_occurred_on_acepta_string_fecha(): void
    {
        $vo = new OccurredOn('2026-03-15 10:00:00');
        $this->assertInstanceOf(DateTimeImmutable::class, $vo->value());
        $this->assertSame('2026-03-15', $vo->value()->format('Y-m-d'));
    }

    public function test_occurred_on_acepta_datetime_immutable(): void
    {
        $dt = new DateTimeImmutable('2026-06-01 08:30:00');
        $vo = new OccurredOn($dt);
        $this->assertSame($dt, $vo->value());
    }

    public function test_occurred_on_to_database_retorna_formato_mysql(): void
    {
        $vo = new OccurredOn('2026-07-20 14:30:00');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $vo->toDatabase());
    }

    // ─── ContratoId ───────────────────────────────────────────────────────

    public function test_contrato_id_acepta_uuid_valido(): void
    {
        $vo = new ContratoId('A1B2C3D4-0000-0000-0000-000000000001');
        $this->assertSame('a1b2c3d4-0000-0000-0000-000000000001', $vo->value());
    }

    public function test_contrato_id_normaliza_a_minusculas(): void
    {
        $vo = new ContratoId('A1B2C3D4-AAAA-BBBB-CCCC-DDDDDDDDDDDD');
        $this->assertSame('a1b2c3d4-aaaa-bbbb-cccc-dddddddddddd', $vo->value());
    }

    public function test_contrato_id_lanza_excepcion_si_vacio(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/cannot be empty/');
        new ContratoId('');
    }

    public function test_contrato_id_lanza_excepcion_si_no_uuid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/UUID/');
        new ContratoId('no-es-un-uuid-valido');
    }

    // ─── EntregaId ────────────────────────────────────────────────────────

    public function test_entrega_id_acepta_uuid_valido(): void
    {
        $vo = new EntregaId('B1C2D3E4-1111-2222-3333-444444444444');
        $this->assertSame('b1c2d3e4-1111-2222-3333-444444444444', $vo->value());
    }

    public function test_entrega_id_lanza_excepcion_si_vacio(): void
    {
        $this->expectException(DomainException::class);
        new EntregaId('');
    }

    public function test_entrega_id_lanza_excepcion_si_no_uuid(): void
    {
        $this->expectException(DomainException::class);
        new EntregaId('no-uuid');
    }

    // ─── DriverId ─────────────────────────────────────────────────────────

    public function test_driver_id_acepta_uuid_valido(): void
    {
        $vo = new DriverId('C2D3E4F5-aaaa-bbbb-cccc-000000000099');
        $this->assertSame('c2d3e4f5-aaaa-bbbb-cccc-000000000099', $vo->value());
    }

    public function test_driver_id_lanza_excepcion_si_vacio(): void
    {
        $this->expectException(DomainException::class);
        new DriverId('');
    }

    public function test_driver_id_lanza_excepcion_si_no_uuid(): void
    {
        $this->expectException(DomainException::class);
        new DriverId('no-uuid');
    }

    // ─── ValueObject::equals ──────────────────────────────────────────────

    public function test_value_object_equals_con_mismo_tipo_y_valor(): void
    {
        $a = new PackageStatus('pendiente');
        $b = new PackageStatus('pendiente');
        $this->assertTrue($a->equals($b));
    }

    public function test_value_object_equals_con_distintos_valores(): void
    {
        $a = new PackageStatus('pendiente');
        $b = new PackageStatus('confirmada');
        $this->assertFalse($a->equals($b));
    }

    // ─── PackageDeliveryTransitionPolicy ─────────────────────────────────

    public function test_policy_permite_transicion_desde_null(): void
    {
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition(null, 'pendiente'));
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition(null, 'en_ruta'));
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition(null, 'confirmada'));
    }

    public function test_policy_permite_transicion_desde_estado_incompleto(): void
    {
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition('pendiente', 'en_ruta'));
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition('en_ruta', 'confirmada'));
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition('fallida', 'pendiente'));
    }

    public function test_policy_confirmada_solo_permite_transicion_a_confirmada(): void
    {
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition('confirmada', 'confirmada'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::canTransition('confirmada', 'en_ruta'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::canTransition('confirmada', 'fallida'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::canTransition('confirmada', 'pendiente'));
    }

    public function test_policy_confirmada_es_mayusculas_no_bloquea_transicion(): void
    {
        // El normalize convierte a lowercase, por lo que 'CONFIRMADA' bloquea transiciones
        $this->assertTrue(PackageDeliveryTransitionPolicy::canTransition('CONFIRMADA', 'confirmada'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::canTransition('CONFIRMADA', 'en_ruta'));
    }

    public function test_policy_is_completed_con_confirmada(): void
    {
        $this->assertTrue(PackageDeliveryTransitionPolicy::isCompleted('confirmada'));
        $this->assertTrue(PackageDeliveryTransitionPolicy::isCompleted('CONFIRMADA'));
    }

    public function test_policy_is_completed_con_otros_estados(): void
    {
        $this->assertFalse(PackageDeliveryTransitionPolicy::isCompleted('pendiente'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::isCompleted('en_ruta'));
        $this->assertFalse(PackageDeliveryTransitionPolicy::isCompleted(null));
        $this->assertFalse(PackageDeliveryTransitionPolicy::isCompleted(''));
    }

    // ─── BaseDomainEvent ──────────────────────────────────────────────────

    public function test_base_domain_event_name_retorna_nombre_de_clase(): void
    {
        $event = new CalendarioCreado('cal-01', new DateTimeImmutable('2026-01-01'));
        $this->assertStringContainsString('CalendarioCreado', $event->name());
    }

    public function test_base_domain_event_occurred_on_es_datetime_immutable(): void
    {
        $event = new CalendarioCreado('cal-02', new DateTimeImmutable('2026-02-01'));
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
    }

    public function test_base_domain_event_aggregate_id_se_preserva(): void
    {
        $event = new CalendarioCreado('cal-99', new DateTimeImmutable('now'));
        $this->assertSame('cal-99', $event->aggregateId());
    }

    // ─── CalendarioCreado::toArray ────────────────────────────────────────

    public function test_calendario_creado_to_array_contiene_fecha(): void
    {
        $fecha = new DateTimeImmutable('2026-05-15T10:00:00Z');
        $event = new CalendarioCreado('cal-10', $fecha);
        $array = $event->toArray();

        $this->assertArrayHasKey('fecha', $array);
        $this->assertStringContainsString('2026-05-15', $array['fecha']);
    }

    // ─── OrdenEntregaCompletada::toArray ──────────────────────────────────

    public function test_orden_entrega_completada_to_array_contiene_todos_campos(): void
    {
        $event = new OrdenEntregaCompletada(
            'op-01',
            'entrega-01',
            'contrato-01',
            5,
            4,
            1,
            new DateTimeImmutable('2026-06-30T12:00:00Z')
        );
        $array = $event->toArray();

        $this->assertSame('op-01', $array['ordenProduccionId']);
        $this->assertSame('entrega-01', $array['entregaId']);
        $this->assertSame('contrato-01', $array['contratoId']);
        $this->assertSame(5, $array['totalPackages']);
        $this->assertSame(4, $array['confirmedPackages']);
        $this->assertSame(1, $array['failedPackages']);
        $this->assertArrayHasKey('completedAt', $array);
    }

    // ─── PaqueteEntregado::toArray ────────────────────────────────────────

    public function test_paquete_entregado_to_array_contiene_todos_campos(): void
    {
        $event = new PaqueteEntregado(
            'op-02',
            'cal-05',
            'con-03',
            'confirmada'
        );
        $array = $event->toArray();

        $this->assertSame('op-02', $array['ordenProduccionId']);
        $this->assertSame('cal-05', $array['calendarioId']);
        $this->assertSame('con-03', $array['contratoId']);
        $this->assertSame('confirmada', $array['estado']);
    }
}
