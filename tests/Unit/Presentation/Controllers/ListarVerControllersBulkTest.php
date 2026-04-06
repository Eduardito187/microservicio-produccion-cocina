<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Controllers;

use App\Domain\Shared\Exception\EntityNotFoundException;
use Tests\TestCase;

/**
 * @class ListarVerControllersBulkTest
 */
class ListarVerControllersBulkTest extends TestCase
{
    /**
     * @dataProvider listarControllersProvider
     */
    public function test_listar_controller_retorna_json_ok(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn([['id' => 'x-1']]);

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke();

        $this->assertSame(200, $response->status());
        $this->assertSame([['id' => 'x-1']], $response->getData(true));
    }

    /**
     * @dataProvider verControllersProvider
     */
    public function test_ver_controller_retorna_json_ok(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn(['id' => 'x-2']);

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke('x-2');

        $this->assertSame(200, $response->status());
        $this->assertSame(['id' => 'x-2'], $response->getData(true));
    }

    /**
     * @dataProvider verControllersProvider
     */
    public function test_ver_controller_retorna_404_si_no_existe(string $controllerClass): void
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

    public static function listarControllersProvider(): array
    {
        return [
            ['App\\Presentation\\Http\\Controllers\\ListarCalendarioItemsController'],
            ['App\\Presentation\\Http\\Controllers\\ListarCalendariosController'],
            ['App\\Presentation\\Http\\Controllers\\ListarDireccionesController'],
            ['App\\Presentation\\Http\\Controllers\\ListarEtiquetasController'],
            ['App\\Presentation\\Http\\Controllers\\ListarPacientesController'],
            ['App\\Presentation\\Http\\Controllers\\ListarPaquetesController'],
            ['App\\Presentation\\Http\\Controllers\\ListarPorcionesController'],
            ['App\\Presentation\\Http\\Controllers\\ListarProductosController'],
            ['App\\Presentation\\Http\\Controllers\\ListarRecetasController'],
            ['App\\Presentation\\Http\\Controllers\\ListarRecetasVersionController'],
            ['App\\Presentation\\Http\\Controllers\\ListarSuscripcionesController'],
            ['App\\Presentation\\Http\\Controllers\\ListarVentanasEntregaController'],
        ];
    }

    public static function verControllersProvider(): array
    {
        return [
            ['App\\Presentation\\Http\\Controllers\\VerCalendarioController'],
            ['App\\Presentation\\Http\\Controllers\\VerCalendarioItemController'],
            ['App\\Presentation\\Http\\Controllers\\VerDireccionController'],
            ['App\\Presentation\\Http\\Controllers\\VerEtiquetaController'],
            ['App\\Presentation\\Http\\Controllers\\VerPacienteController'],
            ['App\\Presentation\\Http\\Controllers\\VerPaqueteController'],
            ['App\\Presentation\\Http\\Controllers\\VerPorcionController'],
            ['App\\Presentation\\Http\\Controllers\\VerProductoController'],
            ['App\\Presentation\\Http\\Controllers\\VerRecetaController'],
            ['App\\Presentation\\Http\\Controllers\\VerRecetaVersionController'],
            ['App\\Presentation\\Http\\Controllers\\VerSuscripcionController'],
            ['App\\Presentation\\Http\\Controllers\\VerVentanaEntregaController'],
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
