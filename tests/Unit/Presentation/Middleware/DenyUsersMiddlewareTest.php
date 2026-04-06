<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Middleware;

use App\Presentation\Http\Middleware\DenyUsersMiddleware;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * @class DenyUsersMiddlewareTest
 */
class DenyUsersMiddlewareTest extends TestCase
{
    public function test_handle_returns_unauthorized_when_token_claims_are_missing(): void
    {
        $this->app->instance('env', 'production');

        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/resource', 'GET');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getData(true)['message'] ?? null);

        $this->app->instance('env', 'testing');
    }

    public function test_handle_returns_forbidden_when_sub_or_username_is_blocked(): void
    {
        $this->app->instance('env', 'production');

        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->attributes->set('token', [
            'sub' => 'user-1',
            'preferred_username' => 'john',
        ]);

        $blockedBySub = $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user-1,other');
        $this->assertSame(403, $blockedBySub->getStatusCode());

        $blockedByUsername = $middleware->handle($request, fn () => response()->json(['ok' => true]), 'john|other');
        $this->assertSame(403, $blockedByUsername->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    public function test_handle_allows_request_when_block_list_is_empty_or_not_matching(): void
    {
        $this->app->instance('env', 'production');

        $middleware = new DenyUsersMiddleware;

        $request = Request::create('/api/resource', 'GET');
        $request->attributes->set('token', [
            'sub' => 'user-allowed',
            'preferred_username' => 'allowed-name',
        ]);

        config(['keycloak.blocked_users' => ['another-user']]);

        $responseNoRouteUsers = $middleware->handle($request, fn () => response()->json(['ok' => true], 200));
        $responseRouteUsers = $middleware->handle($request, fn () => response()->json(['ok' => true], 200), 'u1,u2');

        $this->assertSame(200, $responseNoRouteUsers->getStatusCode());
        $this->assertSame(200, $responseRouteUsers->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    public function test_handle_bypasses_during_unit_tests_without_token(): void
    {
        $middleware = new DenyUsersMiddleware;

        $request = Request::create('/api/anything', 'GET');
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true], 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_private_pact_bypass_helper_detects_header_and_secret_in_local_env(): void
    {
        $this->app->instance('env', 'local');

        putenv('PACT_BYPASS_AUTH=false');
        $_ENV['PACT_BYPASS_AUTH'] = 'false';
        putenv('PACT_BYPASS_HEADER_SECRET=secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'secret';

        $middleware = new DenyUsersMiddleware;

        $request = Request::create('/api/anything', 'GET');
        $request->headers->set('X-Pact-Request', 'true');
        $request->headers->set('X-Pact-Secret', 'secret');

        $ref = new \ReflectionClass($middleware);
        $method = $ref->getMethod('shouldBypassForPact');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($middleware, $request));

        $hasSecret = $ref->getMethod('hasValidPactSecret');
        $hasSecret->setAccessible(true);

        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);

        $requestNoSecret = Request::create('/api/anything', 'GET');
        $this->assertTrue($hasSecret->invoke($middleware, $requestNoSecret));

        $this->app->instance('env', 'testing');
    }

    public function test_handle_passes_through_when_blocked_list_is_empty(): void
    {
        $this->app->instance('env', 'production');
        config(['keycloak.blocked_users' => []]);
        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->attributes->set('token', ['sub' => 'some-user']);
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(200, $response->getStatusCode());
        $this->app->instance('env', 'testing');
    }

    public function test_resolve_blocked_users_returns_empty_when_config_is_not_array(): void
    {
        config(['keycloak.blocked_users' => 'not-an-array']);
        $middleware = new DenyUsersMiddleware;
        $ref = new \ReflectionClass($middleware);
        $method = $ref->getMethod('resolveBlockedUsers');
        $method->setAccessible(true);
        $this->assertSame([], $method->invoke($middleware, ''));
    }

    public function test_has_valid_pact_secret_returns_false_for_wrong_secret(): void
    {
        putenv('PACT_BYPASS_HEADER_SECRET=correct-secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'correct-secret';
        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/any', 'GET');
        $request->headers->set('X-Pact-Secret', 'wrong-secret');
        $ref = new \ReflectionClass($middleware);
        $method = $ref->getMethod('hasValidPactSecret');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($middleware, $request));
        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);
    }

    public function test_should_bypass_for_pact_returns_true_via_header_when_secret_matches(): void
    {
        $this->app->instance('env', 'local');
        putenv('PACT_BYPASS_AUTH');
        unset($_ENV['PACT_BYPASS_AUTH'], $_SERVER['PACT_BYPASS_AUTH']);
        putenv('PACT_BYPASS_HEADER_SECRET=my-secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'my-secret';
        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/any', 'GET');
        $request->headers->set('X-Pact-Request', 'true');
        $request->headers->set('X-Pact-Secret', 'my-secret');
        $ref = new \ReflectionClass($middleware);
        $method = $ref->getMethod('shouldBypassForPact');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($middleware, $request));
        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);
        $this->app->instance('env', 'testing');
    }

    public function test_should_bypass_for_pact_returns_false_when_x_pact_request_absent(): void
    {
        $this->app->instance('env', 'local');
        putenv('PACT_BYPASS_AUTH');
        unset($_ENV['PACT_BYPASS_AUTH'], $_SERVER['PACT_BYPASS_AUTH']);
        $middleware = new DenyUsersMiddleware;
        $request = Request::create('/api/any', 'GET');
        $ref = new \ReflectionClass($middleware);
        $method = $ref->getMethod('shouldBypassForPact');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($middleware, $request));
        $this->app->instance('env', 'testing');
    }
}
