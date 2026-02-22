<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Application\Integration;

use App\Application\Integration\Handlers\PacienteEliminadoHandler;
use App\Application\Support\Transaction\TransactionAggregate;
use App\Domain\Produccion\Repository\PacienteRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @class PacienteEliminadoHandlerTest
 * @package Tests\Unit\Application\Integration
 */
class PacienteEliminadoHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function test_handle_elimina_paciente_por_evento(): void
    {
        $repo = $this->createMock(PacienteRepositoryInterface::class);
        $tx = $this->createMock(TransactionAggregate::class);

        $tx->expects($this->once())
            ->method('runTransaction')
            ->willReturnCallback(function (callable $callback): mixed {
                return $callback();
            });

        $repo->expects($this->once())
            ->method('delete')
            ->with('d9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b');

        $handler = new PacienteEliminadoHandler($repo, $tx);
        $handler->handle([
            'pacienteId' => 'd9cbb4a3-4c2b-4c6e-9d2f-5f9fd6ec1a2b',
        ]);
    }
}
