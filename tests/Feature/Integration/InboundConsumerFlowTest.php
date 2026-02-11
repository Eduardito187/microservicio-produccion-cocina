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
    private function makeMessage(array $decoded, MockObject $channel): AMQPMessage
    {
        $msg = new AMQPMessage(json_encode($decoded));
        $msg->delivery_info = [
            'channel' => $channel,
            'delivery_tag' => 1,
            'routing_key' => 'inbound.key',
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
}
