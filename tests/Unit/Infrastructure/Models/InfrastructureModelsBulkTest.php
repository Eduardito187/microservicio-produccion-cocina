<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Infrastructure\Models;

use App\Infrastructure\Persistence\Model\BaseModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

/**
 * @class InfrastructureModelsBulkTest
 */
class InfrastructureModelsBulkTest extends TestCase
{
    /**
     * @dataProvider modelProvider
     */
    public function test_models_can_be_instantiated_and_expose_table(string $className): void
    {
        $model = new $className;

        $this->assertInstanceOf(BaseModel::class, $model);
        $this->assertNotSame('', $model->getTable());
    }

    /**
     * @dataProvider relationMethodProvider
     */
    public function test_model_relations_return_relation_objects(string $className, string $method): void
    {
        $model = new $className;
        $relation = $model->{$method}();

        $this->assertInstanceOf(Relation::class, $relation);
    }

    public function test_calendario_set_fecha_attribute_normalizes_to_date_string(): void
    {
        $model = new \App\Infrastructure\Persistence\Model\Calendario;
        $model->setFechaAttribute(new \DateTimeImmutable('2026-04-06 14:30:00'));

        $this->assertSame('2026-04-06', $model->getAttributes()['fecha']);
    }

    public function test_calendario_set_fecha_attribute_accepts_null(): void
    {
        $model = new \App\Infrastructure\Persistence\Model\Calendario;
        $model->setFechaAttribute(null);

        $this->assertArrayHasKey('fecha', $model->getAttributes());
        $this->assertNull($model->getAttributes()['fecha']);
    }

    public function test_base_model_assigns_uuid_when_creating_without_id(): void
    {
        $fakeModelClass = new class
        {
            use \App\Infrastructure\Persistence\Model\Concerns\HasUuid;

            public static $creatingCallback;
            public array $attributes = [];

            public static function creating(callable $callback): void
            {
                self::$creatingCallback = $callback;
            }

            public function getKey(): mixed
            {
                return $this->attributes[$this->getKeyName()] ?? null;
            }

            public function getKeyName(): string
            {
                return 'id';
            }

            public function setAttribute(string $name, mixed $value): void
            {
                $this->attributes[$name] = $value;
            }
        };

        $ref = new \ReflectionClass($fakeModelClass);
        $boot = $ref->getMethod('bootHasUuid');
        $boot->setAccessible(true);
        $boot->invoke(null);

        $this->assertIsCallable($fakeModelClass::$creatingCallback);

        ($fakeModelClass::$creatingCallback)($fakeModelClass);
        $this->assertArrayHasKey('id', $fakeModelClass->attributes);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $fakeModelClass->attributes['id']);

        $preserved = clone $fakeModelClass;
        $preserved->attributes['id'] = 'existing-id';
        ($fakeModelClass::$creatingCallback)($preserved);
        $this->assertSame('existing-id', $preserved->attributes['id']);
    }

    public function test_calendario_casts_configuration_is_date_only(): void
    {
        $model = new \App\Infrastructure\Persistence\Model\Calendario;
        $casts = $model->getCasts();

        $this->assertArrayHasKey('fecha', $casts);
        $this->assertSame('date', $casts['fecha']);
    }

    public static function modelProvider(): array
    {
        return [
            [\App\Infrastructure\Persistence\Model\Calendario::class],
            [\App\Infrastructure\Persistence\Model\CalendarioItem::class],
            [\App\Infrastructure\Persistence\Model\Direccion::class],
            [\App\Infrastructure\Persistence\Model\EntregaEvidencia::class],
            [\App\Infrastructure\Persistence\Model\Etiqueta::class],
            [\App\Infrastructure\Persistence\Model\EventStore::class],
            [\App\Infrastructure\Persistence\Model\InboundEvent::class],
            [\App\Infrastructure\Persistence\Model\ItemDespacho::class],
            [\App\Infrastructure\Persistence\Model\KpiOperativo::class],
            [\App\Infrastructure\Persistence\Model\OrdenProduccion::class],
            [\App\Infrastructure\Persistence\Model\OrderDeliveryProgress::class],
            [\App\Infrastructure\Persistence\Model\OrderItem::class],
            [\App\Infrastructure\Persistence\Model\Outbox::class],
            [\App\Infrastructure\Persistence\Model\PackageDeliveryHistory::class],
            [\App\Infrastructure\Persistence\Model\PackageDeliveryTracking::class],
            [\App\Infrastructure\Persistence\Model\Paciente::class],
            [\App\Infrastructure\Persistence\Model\Paquete::class],
            [\App\Infrastructure\Persistence\Model\Porcion::class],
            [\App\Infrastructure\Persistence\Model\ProduccionBatch::class],
            [\App\Infrastructure\Persistence\Model\Product::class],
            [\App\Infrastructure\Persistence\Model\Receta::class],
            [\App\Infrastructure\Persistence\Model\RecetaVersion::class],
            [\App\Infrastructure\Persistence\Model\Suscripcion::class],
            [\App\Infrastructure\Persistence\Model\VentanaEntrega::class],
        ];
    }

    public static function relationMethodProvider(): array
    {
        return [
            [\App\Infrastructure\Persistence\Model\ItemDespacho::class, 'ordenProduccion'],
            [\App\Infrastructure\Persistence\Model\ItemDespacho::class, 'product'],
            [\App\Infrastructure\Persistence\Model\ItemDespacho::class, 'paquete'],
            [\App\Infrastructure\Persistence\Model\ItemDespacho::class, 'calendarioItems'],
            [\App\Infrastructure\Persistence\Model\OrdenProduccion::class, 'items'],
            [\App\Infrastructure\Persistence\Model\OrdenProduccion::class, 'batches'],
            [\App\Infrastructure\Persistence\Model\OrdenProduccion::class, 'despachoItems'],
            [\App\Infrastructure\Persistence\Model\Calendario::class, 'items'],
            [\App\Infrastructure\Persistence\Model\Etiqueta::class, 'suscripcion'],
            [\App\Infrastructure\Persistence\Model\Etiqueta::class, 'paciente'],
            [\App\Infrastructure\Persistence\Model\Etiqueta::class, 'paquete'],
            [\App\Infrastructure\Persistence\Model\Product::class, 'orderItems'],
            [\App\Infrastructure\Persistence\Model\Product::class, 'despachoItems'],
            [\App\Infrastructure\Persistence\Model\Product::class, 'batches'],
            [\App\Infrastructure\Persistence\Model\OrderItem::class, 'ordenProduccion'],
            [\App\Infrastructure\Persistence\Model\OrderItem::class, 'product'],
            [\App\Infrastructure\Persistence\Model\Paciente::class, 'suscripcion'],
            [\App\Infrastructure\Persistence\Model\Paciente::class, 'etiquetas'],
            [\App\Infrastructure\Persistence\Model\Porcion::class, 'batches'],
            [\App\Infrastructure\Persistence\Model\VentanaEntrega::class, 'paquetes'],
            [\App\Infrastructure\Persistence\Model\CalendarioItem::class, 'calendario'],
            [\App\Infrastructure\Persistence\Model\CalendarioItem::class, 'itemDespacho'],
            [\App\Infrastructure\Persistence\Model\Suscripcion::class, 'pacientes'],
            [\App\Infrastructure\Persistence\Model\Suscripcion::class, 'etiquetas'],
            [\App\Infrastructure\Persistence\Model\Direccion::class, 'paquetes'],
            [\App\Infrastructure\Persistence\Model\ProduccionBatch::class, 'ordenProduccion'],
            [\App\Infrastructure\Persistence\Model\ProduccionBatch::class, 'product'],
            [\App\Infrastructure\Persistence\Model\ProduccionBatch::class, 'porcion'],
            [\App\Infrastructure\Persistence\Model\ProduccionBatch::class, 'receta'],
            [\App\Infrastructure\Persistence\Model\Paquete::class, 'etiqueta'],
            [\App\Infrastructure\Persistence\Model\Paquete::class, 'ventana'],
            [\App\Infrastructure\Persistence\Model\Paquete::class, 'direccion'],
            [\App\Infrastructure\Persistence\Model\Paquete::class, 'itemsDespacho'],
            [\App\Infrastructure\Persistence\Model\RecetaVersion::class, 'batches'],
            [\App\Infrastructure\Persistence\Model\RecetaVersion::class, 'etiquetas'],
        ];
    }
}
