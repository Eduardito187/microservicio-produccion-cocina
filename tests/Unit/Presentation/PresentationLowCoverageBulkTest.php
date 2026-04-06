<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation;

use App\Presentation\Http\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class PresentationLowCoverageBulkTest
 */
class PresentationLowCoverageBulkTest extends TestCase
{
    public function test_http_kernel_contains_expected_middleware_aliases_and_groups(): void
    {
        $kernel = new Kernel(app(), app('router'));
        $reflection = new ReflectionClass($kernel);

        $aliasesProp = $reflection->getProperty('middlewareAliases');
        $aliasesProp->setAccessible(true);
        $aliases = $aliasesProp->getValue($kernel);

        $groupsProp = $reflection->getProperty('middlewareGroups');
        $groupsProp->setAccessible(true);
        $groups = $groupsProp->getValue($kernel);

        $this->assertIsArray($aliases);
        $this->assertArrayHasKey('keycloak.jwt', $aliases);
        $this->assertArrayHasKey('role', $aliases);
        $this->assertArrayHasKey('deny.users', $aliases);

        $this->assertIsArray($groups);
        $this->assertArrayHasKey('web', $groups);
        $this->assertArrayHasKey('api', $groups);
    }

    public function test_publish_outbox_command_dispatches_job_and_returns_success(): void
    {
        Bus::fake();

        $this->artisan('outbox:publish')->assertExitCode(0);

        Bus::assertDispatchedSync(\App\Infrastructure\Jobs\PublishOutbox::class);
    }

    public function test_generar_op_controller_returns_201_on_success(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\GenerarOPHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn('op-1');

        $request = $this->createMock(\App\Presentation\Http\Requests\GenerarOPRequest::class);
        $request->expects($this->once())->method('validated')->willReturn([
            'id' => 'op-1',
            'fecha' => '2026-04-06',
            'items' => [
                ['sku' => 'SKU-1', 'qty' => 2],
            ],
        ]);

        $controller = new \App\Presentation\Http\Controllers\GenerarOPController($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(201, $response->status());
        $this->assertSame(['ordenProduccionId' => 'op-1'], $response->getData(true));
    }

    public function test_generar_op_controller_returns_409_for_domain_exception(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\GenerarOPHandler::class);
        $handler->expects($this->once())->method('__invoke')->willThrowException(new \DomainException('conflict'));

        $request = $this->createMock(\App\Presentation\Http\Requests\GenerarOPRequest::class);
        $request->expects($this->once())->method('validated')->willReturn([
            'id' => 'op-1',
            'fecha' => '2026-04-06',
            'items' => [
                ['sku' => 'SKU-1', 'qty' => 2],
            ],
        ]);

        $controller = new \App\Presentation\Http\Controllers\GenerarOPController($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(409, $response->status());
    }

    public function test_planificar_procesar_despachar_controllers_return_201_on_success(): void
    {
        $planHandler = $this->createMock(\App\Application\Produccion\Handler\PlanificadorOPHandler::class);
        $planHandler->method('__invoke')->willReturn('op-plan');

        $procesarHandler = $this->createMock(\App\Application\Produccion\Handler\ProcesadorOPHandler::class);
        $procesarHandler->method('__invoke')->willReturn('op-proc');

        $despacharHandler = $this->createMock(\App\Application\Produccion\Handler\DespachadorOPHandler::class);
        $despacharHandler->method('__invoke')->willReturn('op-desp');

        $planRequest = Mockery::mock(Request::class);
        $planRequest->shouldReceive('validate')->once()->andReturn([
            'ordenProduccionId' => '11111111-1111-4111-8111-111111111111',
            'porcionId' => '22222222-2222-4222-8222-222222222222',
        ]);

        $procRequest = Mockery::mock(Request::class);
        $procRequest->shouldReceive('validate')->once()->andReturn([
            'ordenProduccionId' => '33333333-3333-4333-8333-333333333333',
        ]);

        $despRequest = Mockery::mock(Request::class);
        $despRequest->shouldReceive('validate')->once()->andReturn([
            'ordenProduccionId' => '44444444-4444-4444-8444-444444444444',
            'itemsDespacho' => [['sku' => 'SKU-1']],
            'pacienteId' => '55555555-5555-4555-8555-555555555555',
            'direccionId' => '66666666-6666-4666-8666-666666666666',
            'ventanaEntrega' => '77777777-7777-4777-8777-777777777777',
        ]);

        $planController = new \App\Presentation\Http\Controllers\PlanificarOPController($planHandler);
        $procController = new \App\Presentation\Http\Controllers\ProcesarOPController($procesarHandler);
        $despController = new \App\Presentation\Http\Controllers\DespacharOPController($despacharHandler);

        $this->assertSame(201, $planController->__invoke($planRequest)->status());
        $this->assertSame(201, $procController->__invoke($procRequest)->status());
        $this->assertSame(201, $despController->__invoke($despRequest)->status());
    }

    public function test_planificar_procesar_despachar_controllers_cover_error_branches(): void
    {
        $planHandler = $this->createMock(\App\Application\Produccion\Handler\PlanificadorOPHandler::class);
        $planHandler->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function () {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    throw new \DomainException('plan-conflict');
                }

                throw new \App\Domain\Shared\Exception\EntityNotFoundException('plan-missing');
            });

        $procHandler = $this->createMock(\App\Application\Produccion\Handler\ProcesadorOPHandler::class);
        $procHandler->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function () {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    throw new \DomainException('proc-conflict');
                }

                throw new \App\Domain\Shared\Exception\EntityNotFoundException('proc-missing');
            });

        $despHandler = $this->createMock(\App\Application\Produccion\Handler\DespachadorOPHandler::class);
        $despHandler->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function () {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    throw new \DomainException('desp-conflict');
                }

                throw new \App\Domain\Shared\Exception\EntityNotFoundException('desp-missing');
            });

        $planRequest = Mockery::mock(Request::class);
        $planRequest->shouldReceive('validate')->twice()->andReturn([
            'ordenProduccionId' => '11111111-1111-4111-8111-111111111111',
            'porcionId' => '22222222-2222-4222-8222-222222222222',
        ]);

        $procRequest = Mockery::mock(Request::class);
        $procRequest->shouldReceive('validate')->twice()->andReturn([
            'ordenProduccionId' => '33333333-3333-4333-8333-333333333333',
        ]);

        $despRequest = Mockery::mock(Request::class);
        $despRequest->shouldReceive('validate')->twice()->andReturn([
            'ordenProduccionId' => '44444444-4444-4444-8444-444444444444',
            'itemsDespacho' => [['sku' => 'SKU-1']],
            'pacienteId' => '55555555-5555-4555-8555-555555555555',
            'direccionId' => '66666666-6666-4666-8666-666666666666',
            'ventanaEntrega' => '77777777-7777-4777-8777-777777777777',
        ]);

        $planController = new \App\Presentation\Http\Controllers\PlanificarOPController($planHandler);
        $procController = new \App\Presentation\Http\Controllers\ProcesarOPController($procHandler);
        $despController = new \App\Presentation\Http\Controllers\DespacharOPController($despHandler);

        $this->assertSame(409, $planController->__invoke($planRequest)->status());
        $this->assertSame(404, $planController->__invoke($planRequest)->status());

        $this->assertSame(409, $procController->__invoke($procRequest)->status());
        $this->assertSame(404, $procController->__invoke($procRequest)->status());

        $this->assertSame(409, $despController->__invoke($despRequest)->status());
        $this->assertSame(404, $despController->__invoke($despRequest)->status());
    }

    public function test_crear_calendario_item_controller_returns_201(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\CrearCalendarioItemHandler::class);
        $handler->expects($this->once())->method('__invoke')->willReturn('item-1');

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->once()->andReturn([
            'calendarioId' => '88888888-8888-4888-8888-888888888888',
            'itemDespachoId' => '99999999-9999-4999-8999-999999999999',
        ]);

        $controller = new \App\Presentation\Http\Controllers\CrearCalendarioItemController($handler);
        $response = $controller->__invoke($request);

        $this->assertSame(201, $response->status());
        $this->assertSame(['calendarioItemId' => 'item-1'], $response->getData(true));
    }

    public function test_login_controller_success_and_unavailable_paths(): void
    {
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'client-id',
            'keycloak.client_secret' => null,
            'keycloak.require_dpop' => false,
        ]);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->twice()->andReturn([
            'username' => 'demo',
            'password' => 'secret',
        ]);

        Http::fake([
            '*' => Http::response(['access_token' => 'abc'], 200),
        ]);

        $controller = new \App\Presentation\Http\Controllers\LoginController;
        $okResponse = $controller->__invoke($request);
        $this->assertSame(200, $okResponse->status());

        Http::fake(function () {
            throw new \RuntimeException('network down');
        });

        $unavailable = $controller->__invoke($request);
        $this->assertSame(503, $unavailable->status());
    }

    public function test_refresh_controller_success_and_unavailable_paths(): void
    {
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'client-id',
            'keycloak.client_secret' => null,
            'keycloak.require_dpop' => false,
        ]);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('validate')->twice()->andReturn([
            'refresh_token' => 'r-token',
        ]);

        Http::fake([
            '*' => Http::response(['access_token' => 'refreshed'], 200),
        ]);

        $controller = new \App\Presentation\Http\Controllers\RefreshController;
        $okResponse = $controller->__invoke($request);
        $this->assertSame(200, $okResponse->status());

        Http::fake(function () {
            throw new \RuntimeException('network down');
        });

        $unavailable = $controller->__invoke($request);
        $this->assertSame(503, $unavailable->status());
    }

    public function test_login_and_refresh_controllers_return_non_ok_status_payload_when_provider_rejects(): void
    {
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'client-id',
            'keycloak.client_secret' => 'secret',
            'keycloak.require_dpop' => false,
        ]);

        $loginRequest = Mockery::mock(Request::class);
        $loginRequest->shouldReceive('validate')->once()->andReturn([
            'username' => 'demo',
            'password' => 'bad',
        ]);

        $refreshRequest = Mockery::mock(Request::class);
        $refreshRequest->shouldReceive('validate')->once()->andReturn([
            'refresh_token' => 'expired-token',
        ]);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'Bad credentials',
            ], 401),
        ]);

        $loginController = new \App\Presentation\Http\Controllers\LoginController;
        $refreshController = new \App\Presentation\Http\Controllers\RefreshController;

        $loginResponse = $loginController->__invoke($loginRequest);
        $refreshResponse = $refreshController->__invoke($refreshRequest);

        $this->assertSame(401, $loginResponse->status());
        $this->assertSame('invalid_grant', $loginResponse->getData(true)['error'] ?? null);
        $this->assertSame(401, $refreshResponse->status());
        $this->assertSame('invalid_grant', $refreshResponse->getData(true)['error'] ?? null);
    }

    public function test_login_and_refresh_private_helpers_generate_expected_formats(): void
    {
        $login = new \App\Presentation\Http\Controllers\LoginController;
        $refresh = new \App\Presentation\Http\Controllers\RefreshController;

        $loginRef = new ReflectionClass($login);
        $refreshRef = new ReflectionClass($refresh);

        $encodeLogin = $loginRef->getMethod('base64UrlEncode');
        $encodeLogin->setAccessible(true);
        $loginEncoded = $encodeLogin->invoke($login, 'abc+/=');
        $this->assertIsString($loginEncoded);
        $this->assertStringNotContainsString('+', $loginEncoded);

        $encodeRefresh = $refreshRef->getMethod('base64UrlEncode');
        $encodeRefresh->setAccessible(true);
        $refreshEncoded = $encodeRefresh->invoke($refresh, 'xyz+/=');
        $this->assertIsString($refreshEncoded);
        $this->assertStringNotContainsString('/', $refreshEncoded);

        $buildLogin = $loginRef->getMethod('buildDpopProof');
        $buildLogin->setAccessible(true);
        $dpopLogin = $buildLogin->invoke($login, 'http://keycloak.test/token', 'post');
        $this->assertIsString($dpopLogin);
        $this->assertStringContainsString('.', $dpopLogin);

        $buildRefresh = $refreshRef->getMethod('buildDpopProof');
        $buildRefresh->setAccessible(true);
        $dpopRefresh = $buildRefresh->invoke($refresh, 'http://keycloak.test/token', 'POST');
        $this->assertIsString($dpopRefresh);
        $this->assertStringContainsString('.', $dpopRefresh);
    }

    public function test_event_bus_controller_unauthorized_duplicate_and_validation_error(): void
    {
        putenv('EVENTBUS_SECRET=evt-secret');
        $_ENV['EVENTBUS_SECRET'] = 'evt-secret';

        $handler = $this->createMock(\App\Application\Produccion\Handler\RegistrarInboundEventHandler::class);
        $handler->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function () {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return true;
                }

                throw new \InvalidArgumentException('bad envelope');
            });

        $controller = new \App\Presentation\Http\Controllers\EventBusController($handler);

        $unauthorizedRequest = Mockery::mock(Request::class);
        $unauthorizedRequest->shouldReceive('header')->with('X-EventBus-Token')->andReturn('wrong');
        $unauthorized = $controller->__invoke($unauthorizedRequest);
        $this->assertSame(401, $unauthorized->status());

        $duplicateRequest = Mockery::mock(Request::class);
        $duplicateRequest->shouldReceive('header')->with('X-EventBus-Token')->andReturn('evt-secret');
        $duplicateRequest->shouldReceive('validate')->andReturn([
            'event' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'occurred_on' => '2026-04-06T12:00:00Z',
            'payload' => ['id' => '1'],
            'schema_version' => 1,
            'event_id' => '11111111-1111-4111-8111-111111111111',
            'correlation_id' => '22222222-2222-4222-8222-222222222222',
        ]);
        $duplicate = $controller->__invoke($duplicateRequest);
        $this->assertSame(200, $duplicate->status());

        $validationRequest = Mockery::mock(Request::class);
        $validationRequest->shouldReceive('header')->with('X-EventBus-Token')->andReturn('evt-secret');
        $validationRequest->shouldReceive('validate')->andReturn([
            'event' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'occurred_on' => '2026-04-06T12:00:00Z',
            'payload' => ['id' => '1'],
            'schema_version' => 1,
            'event_id' => '33333333-3333-4333-8333-333333333333',
            'correlation_id' => '44444444-4444-4444-8444-444444444444',
        ]);
        $validationError = $controller->__invoke($validationRequest);
        $this->assertSame(422, $validationError->status());
    }

    public function test_event_bus_controller_ok_response_and_hash_envelope_helper(): void
    {
        putenv('EVENTBUS_SECRET=evt-secret-ok');
        $_ENV['EVENTBUS_SECRET'] = 'evt-secret-ok';

        $handler = $this->createMock(\App\Application\Produccion\Handler\RegistrarInboundEventHandler::class);
        $eventsToTest = [
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCerrada',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionPlanificada',
            'App\\Domain\\Produccion\\Events\\OrdenProduccionProcesada',
            'App\\Domain\\Produccion\\Events\\ProduccionBatchCreado',
            'App\\Domain\\Produccion\\Events\\EventoNoMapeado',
        ];

        $handler->expects($this->exactly(count($eventsToTest)))->method('__invoke')->willReturn(false);

        $controller = new \App\Presentation\Http\Controllers\EventBusController($handler);

        foreach ($eventsToTest as $eventName) {
            $okRequest = Mockery::mock(Request::class);
            $okRequest->shouldReceive('header')->with('X-EventBus-Token')->andReturn('evt-secret-ok');
            $okRequest->shouldReceive('validate')->andReturn([
                'event' => $eventName,
                'occurred_on' => '2026-04-06T12:00:00Z',
                'payload' => ['id' => '1'],
                'schema_version' => 1,
                'event_id' => '11111111-1111-4111-8111-111111111111',
                'correlation_id' => '22222222-2222-4222-8222-222222222222',
            ]);

            $ok = $controller->__invoke($okRequest);
            $this->assertSame(200, $ok->status());
            $this->assertSame('ok', $ok->getData(true)['status'] ?? null);
        }

        $ref = new ReflectionClass($controller);
        $hashMethod = $ref->getMethod('hashEnvelope');
        $hashMethod->setAccessible(true);
        $hash = $hashMethod->invoke($controller, [
            'event' => 'App\\Domain\\Produccion\\Events\\OrdenProduccionCreada',
            'occurred_on' => '2026-04-06T12:00:00Z',
            'payload' => ['id' => '1'],
        ]);

        $this->assertIsString($hash);
        $this->assertSame(64, strlen($hash));
    }

    public function test_actualizar_calendario_item_controller_success_and_not_found(): void
    {
        $handler = $this->createMock(\App\Application\Produccion\Handler\ActualizarCalendarioItemHandler::class);
        $handler->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturnCallback(function () {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    return 'item-updated';
                }

                throw new \App\Domain\Shared\Exception\EntityNotFoundException('missing');
            });

        $controller = new \App\Presentation\Http\Controllers\ActualizarCalendarioItemController($handler);

        $okRequest = Mockery::mock(Request::class);
        $okRequest->shouldReceive('validate')->andReturn([
            'calendarioId' => '11111111-1111-4111-8111-111111111111',
            'itemDespachoId' => '22222222-2222-4222-8222-222222222222',
        ]);
        $okResponse = $controller->__invoke($okRequest, 'item-id');
        $this->assertSame(200, $okResponse->status());

        $missingRequest = Mockery::mock(Request::class);
        $missingRequest->shouldReceive('validate')->andReturn([
            'calendarioId' => '33333333-3333-4333-8333-333333333333',
            'itemDespachoId' => '44444444-4444-4444-8444-444444444444',
        ]);
        $missingResponse = $controller->__invoke($missingRequest, 'missing-id');
        $this->assertSame(404, $missingResponse->status());
    }

    public function test_proxy_controller_redirects_to_external_urls(): void
    {
        $controller = new \App\Presentation\Http\Controllers\ProxyController;

        $this->assertSame('https://jsonplaceholder.typicode.com/users', $controller->users()->getTargetUrl());
        $this->assertSame('https://jsonplaceholder.typicode.com/posts', $controller->posts()->getTargetUrl());
    }

    public function test_generar_op_request_authorize_and_rules(): void
    {
        $request = new \App\Presentation\Http\Requests\GenerarOPRequest;

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertArrayHasKey('fecha', $rules);
        $this->assertArrayHasKey('items', $rules);
        $this->assertArrayHasKey('items.*.sku', $rules);
        $this->assertArrayHasKey('items.*.qty', $rules);
    }

    public function test_redirect_if_authenticated_branches(): void
    {
        $middleware = new \App\Presentation\Http\Middleware\RedirectIfAuthenticated;
        $request = Request::create('/login', 'GET');

        Auth::shouldReceive('guard')->with(null)->once()->andReturn(new class
        {
            public function check(): bool
            {
                return false;
            }
        });

        $nextResponse = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(200, $nextResponse->getStatusCode());

        Auth::shouldReceive('guard')->with('web')->once()->andReturn(new class
        {
            public function check(): bool
            {
                return true;
            }
        });

        $redirectResponse = $middleware->handle($request, fn () => response('ok', 200), 'web');
        $this->assertSame(302, $redirectResponse->getStatusCode());
    }

    public function test_middleware_and_provider_light_components_execute_methods(): void
    {
        $auth = app(\App\Presentation\Http\Middleware\Authenticate::class);
        $authReflection = new ReflectionClass($auth);
        $redirectTo = $authReflection->getMethod('redirectTo');
        $redirectTo->setAccessible(true);

        $jsonRequest = Request::create('/api', 'GET');
        $jsonRequest->headers->set('Accept', 'application/json');
        $this->assertNull($redirectTo->invoke($auth, $jsonRequest));

        $trustHosts = app(\App\Presentation\Http\Middleware\TrustHosts::class);
        $this->assertIsArray($trustHosts->hosts());

        $trustProxies = new \App\Presentation\Http\Middleware\TrustProxies;
        $tpRef = new ReflectionClass($trustProxies);
        $headersProp = $tpRef->getProperty('headers');
        $headersProp->setAccessible(true);
        $this->assertIsInt($headersProp->getValue($trustProxies));

        $validateSignature = new \App\Presentation\Http\Middleware\ValidateSignature;
        $vsRef = new ReflectionClass($validateSignature);
        $exceptProp = $vsRef->getProperty('except');
        $exceptProp->setAccessible(true);
        $this->assertIsArray($exceptProp->getValue($validateSignature));

        $provider = new \App\Presentation\Providers\BroadcastServiceProvider(app());
        $provider->boot();
        $this->assertTrue(true);
    }

    public function test_console_kernel_schedule_registers_outbox_publish_command(): void
    {
        $kernel = app(\App\Presentation\Console\Kernel::class);
        $schedule = app(Schedule::class);

        $ref = new ReflectionClass($kernel);
        $scheduleMethod = $ref->getMethod('schedule');
        $scheduleMethod->setAccessible(true);
        $scheduleMethod->invoke($kernel, $schedule);

        $events = $schedule->events();
        $this->assertNotEmpty($events);
        $found = false;

        foreach ($events as $event) {
            if (str_contains((string) $event->command, 'outbox:publish')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function test_deny_users_helper_methods_cover_parsing_and_secrets(): void
    {
        $middleware = new \App\Presentation\Http\Middleware\DenyUsersMiddleware;
        $ref = new ReflectionClass($middleware);

        $parse = $ref->getMethod('parseUsers');
        $parse->setAccessible(true);
        $this->assertSame(['u1', 'u2', 'u3'], $parse->invoke($middleware, 'u1|u2,u3'));

        config(['keycloak.blocked_users' => [' a ', 'b', '', null]]);
        $resolve = $ref->getMethod('resolveBlockedUsers');
        $resolve->setAccessible(true);
        $this->assertSame(['a', 'b'], $resolve->invoke($middleware, ''));

        putenv('PACT_BYPASS_HEADER_SECRET=sec');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'sec';

        $request = Request::create('/api/_pact/state', 'GET');
        $request->headers->set('X-Pact-Secret', 'sec');

        $hasValid = $ref->getMethod('hasValidPactSecret');
        $hasValid->setAccessible(true);
        $this->assertTrue($hasValid->invoke($middleware, $request));
    }
}
