<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Controllers;

use App\Domain\Shared\Exception\EntityNotFoundException;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * @class CrearActualizarControllersBulkTest
 */
class CrearActualizarControllersBulkTest extends TestCase
{
    /**
     * @dataProvider crearControllersProvider
     */
    public function test_crear_controller_retorna_201(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn('generated-id');

        $request = $this->requestWithPayload($this->fullPayload());

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(201, $response->status());
    }

    /**
     * @dataProvider actualizarControllersProvider
     */
    public function test_actualizar_controller_retorna_200(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn('updated-id');

        $request = $this->requestWithPayload($this->fullPayload());

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke($request, 'existing-id');

        $this->assertSame(200, $response->status());
    }

    /**
     * @dataProvider actualizarControllersProvider
     */
    public function test_actualizar_controller_retorna_404_si_no_existe(string $controllerClass): void
    {
        $handlerClass = $this->handlerClassOf($controllerClass);
        $handler = $this->createMock($handlerClass);
        $handler->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new EntityNotFoundException('no existe'));

        $request = $this->requestWithPayload($this->fullPayload());

        $controller = new $controllerClass($handler);
        $response = $controller->__invoke($request, 'missing-id');

        $this->assertSame(404, $response->status());
        $this->assertSame(['message' => 'no existe'], $response->getData(true));
    }

    public function test_crear_receta_controller_retorna_422_si_no_hay_nombre(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\CrearRecetaHandler::class);
        $handler->expects($this->never())->method('__invoke');

        $request = $this->requestWithPayload([
            'nutrientes' => ['kcal' => 100],
            'ingredientes' => [['agua']],
        ]);

        $controller = new \App\Presentation\Http\Controllers\CrearRecetaController($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(422, $response->status());
    }

    public function test_crear_receta_version_controller_retorna_422_si_no_hay_nombre(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\CrearRecetaVersionHandler::class);
        $handler->expects($this->never())->method('__invoke');

        $request = $this->requestWithPayload([
            'nutrientes' => ['kcal' => 100],
            'ingredientes' => [['agua']],
        ]);

        $controller = new \App\Presentation\Http\Controllers\CrearRecetaVersionController($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(422, $response->status());
    }

    public function test_actualizar_receta_controller_retorna_422_si_no_hay_nombre(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\ActualizarRecetaHandler::class);
        $handler->expects($this->never())->method('__invoke');

        $request = $this->requestWithPayload([
            'nutrientes' => ['kcal' => 100],
            'ingredientes' => [['agua']],
        ]);

        $controller = new \App\Presentation\Http\Controllers\ActualizarRecetaController($handler);
        $response = $controller->__invoke($request, 'rec-id');

        $this->assertSame(422, $response->status());
    }

    public function test_actualizar_receta_version_controller_retorna_422_si_no_hay_nombre(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\ActualizarRecetaVersionHandler::class);
        $handler->expects($this->never())->method('__invoke');

        $request = $this->requestWithPayload([
            'nutrientes' => ['kcal' => 100],
            'ingredientes' => [['agua']],
        ]);

        $controller = new \App\Presentation\Http\Controllers\ActualizarRecetaVersionController($handler);
        $response = $controller->__invoke($request, 'rec-id');

        $this->assertSame(422, $response->status());
    }

    public static function crearControllersProvider(): array
    {
        return [
            ['App\\Presentation\\Http\\Controllers\\CrearCalendarioController'],
            ['App\\Presentation\\Http\\Controllers\\CrearDireccionController'],
            ['App\\Presentation\\Http\\Controllers\\CrearEtiquetaController'],
            ['App\\Presentation\\Http\\Controllers\\CrearPacienteController'],
            ['App\\Presentation\\Http\\Controllers\\CrearPaqueteController'],
            ['App\\Presentation\\Http\\Controllers\\CrearPorcionController'],
            ['App\\Presentation\\Http\\Controllers\\CrearProductoController'],
            ['App\\Presentation\\Http\\Controllers\\CrearRecetaController'],
            ['App\\Presentation\\Http\\Controllers\\CrearRecetaVersionController'],
            ['App\\Presentation\\Http\\Controllers\\CrearSuscripcionController'],
            ['App\\Presentation\\Http\\Controllers\\CrearVentanaEntregaController'],
        ];
    }

    public static function actualizarControllersProvider(): array
    {
        return [
            ['App\\Presentation\\Http\\Controllers\\ActualizarCalendarioController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarDireccionController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarEtiquetaController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarPacienteController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarPaqueteController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarPorcionController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarProductoController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarRecetaController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarRecetaVersionController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarSuscripcionController'],
            ['App\\Presentation\\Http\\Controllers\\ActualizarVentanaEntregaController'],
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

    private function requestWithPayload(array $payload): Request
    {
        return Request::create('/dummy', 'POST', $payload);
    }

    private function fullPayload(): array
    {
        return [
            'nombre' => 'Nombre Demo',
            'name' => 'Name Demo',
            'documento' => 'DOC-001',
            'suscripcionId' => null,
            'etiquetaId' => null,
            'ventanaId' => null,
            'direccionId' => null,
            'pacienteId' => null,
            'tipoServicio' => 'Mensual',
            'fechaInicio' => '2026-01-01',
            'fechaFin' => '2026-02-01',
            'estado' => 1,
            'fecha' => '2026-03-01',
            'desde' => '2026-03-01 08:00:00',
            'hasta' => '2026-03-01 12:00:00',
            'entregaId' => '66666666-6666-6666-6666-666666666666',
            'contratoId' => '77777777-7777-7777-7777-777777777777',
            'calendarioId' => '88888888-8888-8888-8888-888888888888',
            'itemDespachoId' => '99999999-9999-9999-9999-999999999999',
            'linea1' => 'Calle Demo 1',
            'linea2' => 'Apto 2',
            'ciudad' => 'Bogota',
            'provincia' => 'Cundinamarca',
            'pais' => 'CO',
            'geo' => ['lat' => 4.6, 'lng' => -74.0],
            'qrPayload' => ['code' => 'qr-1'],
            'sku' => 'SKU-001',
            'price' => 10.5,
            'specialPrice' => 9.5,
            'pesoGr' => 250,
            'nutrientes' => ['kcal' => 500],
            'ingredientes' => [['item' => 'agua']],
            'ingredients' => [['item' => 'agua']],
            'description' => 'Descripcion demo',
            'instructions' => 'Instrucciones demo',
            'totalCalories' => 500,
        ];
    }
}
