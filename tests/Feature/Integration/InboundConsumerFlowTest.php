<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Feature\Integration;

use App\Presentation\Console\Commands\ConsumeRabbitMq;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Integration\IntegrationEventRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * @class InboundConsumerFlowTest
 * @package Tests\Feature\Integration
 */
class InboundConsumerFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param array $decoded
     * @param MockObject $channel
     * @return AMQPMessage
     */
    private function makeMessage(array $decoded, MockObject $channel, string $routingKey = 'inbound.key'): AMQPMessage
    {
        $msg = new AMQPMessage(json_encode($decoded));
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 1,
            'routing_key' => $routingKey,
        ];
        return $msg;
    }

    /**
     * @return void
     */
    public function test_consumer_flow_happy_path(): void
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch');
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'event' => 'DireccionCreada',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'direccionId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            ],
        ], $channel);

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_flow_payload_invalido_envia_nack(): void
    {
        config(['rabbitmq.inbound.max_retries' => 0]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->never())->method('__invoke');
        $router->expects($this->never())->method('dispatch');
        $channel->expects($this->never())->method('basic_ack');
        $channel->expects($this->once())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'event' => 'DireccionCreada',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                // missing direccionId
            ],
        ], $channel);

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_por_routing_key_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'paciente.paciente-creado' => 'PacienteCreado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'PacienteCreado',
                $this->isType('array'),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'nombre' => 'Juan Perez',
            ],
        ], $channel, 'paciente.paciente-creado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_paciente_eliminado_por_routing_key_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'paciente.paciente-eliminado' => 'PacienteEliminado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'PacienteEliminado',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['pacienteId'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            ],
        ], $channel, 'paciente.paciente-eliminado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_paciente_eliminado_por_event_name_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'PatientDeletedEvent' => 'PacienteEliminado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'PacienteEliminado',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['pacienteId'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'event_name' => 'PatientDeletedEvent',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            ],
        ], $channel, 'paciente.paciente-eliminado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_acepta_mensaje_sin_envelope_usando_routing_key(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'paciente.paciente-creado' => 'PacienteCreado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'PacienteCreado',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['pacienteId'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
            'nombre' => 'Juan Perez',
            'documento' => '1234567',
            'suscripcionId' => 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1b3c',
        ], $channel, 'paciente.paciente-creado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_suscripcion_por_routing_key_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'suscripciones.suscripcion-actualizada' => 'SuscripcionActualizada',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'SuscripcionActualizada',
                $this->isType('array'),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'suscripcionId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'nombre' => 'Plan 15 dias',
            ],
        ], $channel, 'suscripciones.suscripcion-actualizada');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_contrato_creado_por_routing_key_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'contrato.creado' => 'contrato.creado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'contrato.creado',
                $this->isType('array'),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'contratoId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'pacienteId' => 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
                'tipoServicio' => 'Plan 30 dias',
                'fechaInicio' => '2026-02-01',
                'fechaFin' => '2026-03-02',
            ],
        ], $channel, 'contrato.creado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_contrato_cancelado_por_routing_key_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'contrato.cancelado' => 'contrato.cancelado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'contrato.cancelado',
                $this->isType('array'),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'contratoId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'motivoCancelacion' => 'solicitud del paciente',
            ],
        ], $channel, 'contrato.cancelado');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_receta_por_routing_key_alias_con_nuevo_payload(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'planes.receta-actualizada' => 'RecetaActualizada',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'RecetaActualizada',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['id'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b'
                        && ($payload['name'] ?? null) === 'Ensalada Proteica';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);

        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-01-10T10:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'id' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'name' => 'Ensalada Proteica',
                'description' => 'Receta rica en proteina',
                'instructions' => 'Mezclar ingredientes',
                'totalCalories' => 420,
                'ingredients' => [
                    [
                        'idIngredient' => 'f9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
                        'quantity' => 150,
                    ],
                ],
            ],
        ], $channel, 'planes.receta-actualizada');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_calendario_crear_dia_por_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'calendarios.crear-dia' => 'CalendarioEntregaCreado',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'CalendarioEntregaCreado',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['calendarioId'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b'
                        && ($payload['fecha'] ?? null) === '2026-02-12T00:00:00Z';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);
        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-02-12T00:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'calendarioId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'fecha' => '2026-02-12T00:00:00Z',
            ],
        ], $channel, 'calendarios.crear-dia');

        $command->testProcessMessage($msg);
    }

    /**
     * @return void
     */
    public function test_consumer_resuelve_evento_calendario_direccion_cambiada_por_alias(): void
    {
        config([
            'rabbitmq.inbound.event_aliases' => [
                'calendarios.direccion-entrega-cambiada' => 'DireccionEntregaCambiada',
            ],
        ]);

        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        $channel = $this->getMockBuilder(\PhpAmqpLib\Channel\AMQPChannel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['basic_ack', 'basic_nack'])
            ->getMock();

        $handler->expects($this->once())->method('__invoke')->willReturn(false);
        $router->expects($this->once())->method('dispatch')
            ->with(
                'DireccionEntregaCambiada',
                $this->callback(function ($payload): bool {
                    return is_array($payload)
                        && ($payload['paqueteId'] ?? null) === 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b'
                        && ($payload['direccionId'] ?? null) === 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1b3c';
                }),
                $this->arrayHasKey('routing_key')
            );
        $channel->expects($this->once())->method('basic_ack');
        $channel->expects($this->never())->method('basic_nack');

        $command = new ConsumeRabbitMq($handler, $router);
        $msg = $this->makeMessage([
            'event_id' => 'e28e9cc2-5225-40c0-b88b-2341f96d76a3',
            'occurred_on' => '2026-02-12T00:00:00Z',
            'schema_version' => 1,
            'correlation_id' => '0fec65f5-9b0c-49c4-bfb3-9b8f29c3f1d4',
            'payload' => [
                'paqueteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
                'itemDespachoId' => 'f9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2c',
                'direccionId' => 'a9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1b3c',
            ],
        ], $channel, 'calendarios.direccion-entrega-cambiada');

        $command->testProcessMessage($msg);
    }
}
