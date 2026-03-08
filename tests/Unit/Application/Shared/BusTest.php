<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Shared;

use App\Application\Shared\BusInterface;
use App\Application\Shared\SimpleEventPublisher;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @class BusTest
 */
class BusTest extends TestCase
{
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
