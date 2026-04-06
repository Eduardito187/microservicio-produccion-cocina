<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Middleware;

use App\Presentation\Http\Middleware\RequireRoleMiddleware;
use Illuminate\Http\Request;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class RequireRoleMiddlewareTest
 */
class RequireRoleMiddlewareTest extends TestCase
{
    public function test_parse_roles_splits_pipes_and_commas_and_deduplicates(): void
    {
        $middleware = new RequireRoleMiddleware;

        $parsed = $this->invokePrivate($middleware, 'parseRoles', [['admin|ops', 'viewer,admin']]);

        $this->assertSame(['admin', 'ops', 'viewer'], $parsed);
    }

    public function test_extract_roles_reads_realm_and_client_roles(): void
    {
        config(['keycloak.client_id' => 'api-gateway']);

        $middleware = new RequireRoleMiddleware;
        $claims = [
            'realm_access' => ['roles' => ['realm-role']],
            'resource_access' => [
                'api-gateway' => ['roles' => ['client-role']],
            ],
        ];

        $roles = $this->invokePrivate($middleware, 'extractRoles', [$claims]);

        $this->assertSame(['realm-role', 'client-role'], $roles);
    }

    public function test_to_array_handles_array_object_and_scalar_values(): void
    {
        $middleware = new RequireRoleMiddleware;

        $this->assertSame(['a' => 1], $this->invokePrivate($middleware, 'toArray', [['a' => 1]]));
        $this->assertSame(['a' => 1], $this->invokePrivate($middleware, 'toArray', [(object) ['a' => 1]]));
        $this->assertSame([], $this->invokePrivate($middleware, 'toArray', ['not-array']));
    }

    public function test_has_valid_pact_secret_behaviour(): void
    {
        $middleware = new RequireRoleMiddleware;

        putenv('PACT_BYPASS_HEADER_SECRET=super-secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'super-secret';

        $validRequest = Request::create('/api/_pact/state', 'GET');
        $validRequest->headers->set('X-Pact-Secret', 'super-secret');

        $invalidRequest = Request::create('/api/_pact/state', 'GET');
        $invalidRequest->headers->set('X-Pact-Secret', 'wrong-secret');

        $this->assertTrue($this->invokePrivate($middleware, 'hasValidPactSecret', [$validRequest]));
        $this->assertFalse($this->invokePrivate($middleware, 'hasValidPactSecret', [$invalidRequest]));
    }

    public function test_should_bypass_for_pact_when_env_flag_enabled_and_path_matches(): void
    {
        $middleware = new RequireRoleMiddleware;

        putenv('PACT_BYPASS_AUTH=true');
        $_ENV['PACT_BYPASS_AUTH'] = 'true';

        $pactRequest = Request::create('/api/_pact/state', 'GET');
        $normalRequest = Request::create('/api/products', 'GET');

        $this->assertTrue($this->invokePrivate($middleware, 'shouldBypassForPact', [$pactRequest]));
        $this->assertFalse($this->invokePrivate($middleware, 'shouldBypassForPact', [$normalRequest]));
    }

    public function test_extract_roles_handles_non_array_claim_shapes_and_missing_client(): void
    {
        config(['keycloak.client_id' => 'api-gateway']);

        $middleware = new RequireRoleMiddleware;
        $claims = [
            'realm_access' => (object) ['roles' => ['realm-only']],
            'resource_access' => [
                'other-client' => ['roles' => ['ignored']],
            ],
        ];

        $roles = $this->invokePrivate($middleware, 'extractRoles', [$claims]);

        $this->assertSame(['realm-only'], $roles);
    }

    public function test_has_valid_pact_secret_returns_true_when_secret_not_configured(): void
    {
        $middleware = new RequireRoleMiddleware;

        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);

        $request = Request::create('/api/_pact/state', 'GET');

        $this->assertTrue($this->invokePrivate($middleware, 'hasValidPactSecret', [$request]));
    }

    public function test_handle_returns_unauthorized_for_missing_token_and_forbidden_for_missing_role(): void
    {
        $this->app->instance('env', 'production');

        config(['keycloak.client_id' => 'api-gateway']);

        $middleware = new RequireRoleMiddleware;

        $requestUnauthorized = Request::create('/api/resource', 'GET');
        $responseUnauthorized = $middleware->handle($requestUnauthorized, fn () => response('ok', 200), 'admin');
        $this->assertSame(401, $responseUnauthorized->getStatusCode());

        $requestForbidden = Request::create('/api/resource', 'GET');
        $requestForbidden->attributes->set('token', [
            'realm_access' => ['roles' => ['viewer']],
            'resource_access' => ['api-gateway' => ['roles' => ['reader']]],
            'sub' => 'u-1',
        ]);

        $responseForbidden = $middleware->handle($requestForbidden, fn () => response('ok', 200), 'admin');
        $this->assertSame(403, $responseForbidden->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    public function test_handle_allows_when_required_role_exists_or_no_roles_required(): void
    {
        $this->app->instance('env', 'production');

        config(['keycloak.client_id' => 'api-gateway']);

        $middleware = new RequireRoleMiddleware;

        $request = Request::create('/api/resource', 'GET');
        $request->attributes->set('token', [
            'realm_access' => ['roles' => ['admin']],
            'resource_access' => ['api-gateway' => ['roles' => ['reader']]],
        ]);

        $responseWithRole = $middleware->handle($request, fn () => response('ok', 200), 'admin');
        $responseNoRoles = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $responseWithRole->getStatusCode());
        $this->assertSame(200, $responseNoRoles->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }
}
