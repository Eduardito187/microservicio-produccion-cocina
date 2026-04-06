<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Controllers;

use App\Domain\Shared\Exception\EntityNotFoundException;
use Tests\TestCase;

/**
 * @class EliminarControllersBulkTest
 */
class EliminarControllersBulkTest extends TestCase
{
    /**
     * @dataProvider eliminarControllersProvider
     */
    public function test_eliminar_controller_retorna_204(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke');

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke('to-delete-id');

        $this->assertSame(204, $response->status());
    }

    /**
     * @dataProvider eliminarControllersProvider
     */
    public function test_eliminar_controller_retorna_404_si_no_existe(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new EntityNotFoundException('no existe'));

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke('missing-id');

        $this->assertSame(404, $response->status());
        $this->assertSame(['message' => 'no existe'], $response->getData(true));
    }

    public static function eliminarControllersProvider(): array
    {
        return [
            ['App\\Presentation\\Http\\Controllers\\EliminarCalendarioController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarCalendarioItemController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarDireccionController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarEtiquetaController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarPacienteController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarPaqueteController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarPorcionController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarProductoController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarRecetaController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarRecetaVersionController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarSuscripcionController'],
            ['App\\Presentation\\Http\\Controllers\\EliminarVentanaEntregaController'],
        ];
    }

    private function handlerClassOf(string $controllerClass): string
    {
        $reflection = new \ReflectionClass($controllerClass);
        $ctor = $reflection->getConstructor();
        $param = $ctor?->getParameters()[0] ?? null;
        $type = $param?->getType();

        if (! $type instanceof \ReflectionNamedType) {
            $this->fail('Controller constructor does not expose a named handler type: ' . $controllerClass);
        }

        return $type->getName();
    }
}
