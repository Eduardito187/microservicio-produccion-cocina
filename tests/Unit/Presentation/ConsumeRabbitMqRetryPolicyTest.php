<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation;

use App\Presentation\Console\Commands\ConsumeRabbitMq;
use App\Application\Produccion\Handler\RegistrarInboundEventHandler;
use App\Application\Integration\IntegrationEventRouter;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @class ConsumeRabbitMqRetryPolicyTest
 * @package Tests\Unit\Presentation
 */
class ConsumeRabbitMqRetryPolicyTest extends TestCase
{
    /**
     * @return ConsumeRabbitMq
     */
    private function makeCommand(): ConsumeRabbitMq
    {
        $handler = $this->createMock(RegistrarInboundEventHandler::class);
        $router = $this->createMock(IntegrationEventRouter::class);
        return new ConsumeRabbitMq($handler, $router);
    }

    /**
     * @return void
     */
    public function test_get_retry_count_uses_x_death_count(): void
    {
        $command = $this->makeCommand();
        $ref = new ReflectionClass($command);
        $method = $ref->getMethod('getRetryCount');
        $method->setAccessible(true);

        $headers = new AMQPTable([
            'x-death' => [
                ['count' => 2],
            ],
        ]);
        $msg = new AMQPMessage('{"x":1}', ['application_headers' => $headers]);

        $count = $method->invoke($command, $msg);
        $this->assertSame(2, $count);
    }

    /**
     * @return void
     */
    public function test_resolve_retry_delay_uses_configured_list(): void
    {
        $command = $this->makeCommand();
        $ref = new ReflectionClass($command);
        $method = $ref->getMethod('resolveRetryDelay');
        $method->setAccessible(true);

        $delay0 = $method->invoke($command, 0);
        $delay1 = $method->invoke($command, 1);
        $delay2 = $method->invoke($command, 2);

        $this->assertSame(10, $delay0);
        $this->assertSame(60, $delay1);
        $this->assertSame(300, $delay2);
    }
}
