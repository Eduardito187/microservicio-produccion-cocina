<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Shared;

use App\Application\Shared\SimpleEventPublisher;
use App\Application\Shared\BusInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

/**
 * @class BusTest
 * @package Tests\Unit\Application\Shared
 */
class BusTest extends TestCase
{
    /**
     * @return void
     */
    public function test_publica_evento_con_datos_correctos(): void
    {
        $bus = $this->createMock(BusInterface::class);
        $bus->expects($this->once())->method('publish')
            ->with(
                $this->equalTo('evt-1'),
                $this->equalTo('EventoX'),
                $this->equalTo(['x' => 1]),
                $this->isInstanceOf(DateTimeImmutable::class)
            );
        $publisher = new SimpleEventPublisher($bus);
        $publisher->publish('evt-1', 'EventoX', ['x' => 1]);
    }
}
