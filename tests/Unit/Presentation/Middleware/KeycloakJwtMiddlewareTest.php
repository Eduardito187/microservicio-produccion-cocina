<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace Tests\Unit\Presentation\Middleware;

use App\Presentation\Http\Middleware\KeycloakJwtMiddleware;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

/**
 * @class KeycloakJwtMiddlewareTest
 */
class KeycloakJwtMiddlewareTest extends TestCase
{
    public function test_is_valid_audience_accepts_expected_values(): void
    {
        config(['keycloak.client_id' => 'api-gateway']);
        $middleware = new KeycloakJwtMiddleware;

        $this->assertTrue($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => 'api-gateway',
        ]]));

        $this->assertTrue($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => 'account',
            'azp' => 'api-gateway',
        ]]));

        $this->assertTrue($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => ['something', 'api-gateway'],
        ]]));

        $this->assertFalse($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => 'other-client',
        ]]));
    }

    public function test_jwk_thumbprint_and_base64_helpers_work_as_expected(): void
    {
        $middleware = new KeycloakJwtMiddleware;

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => 'x-value',
            'y' => 'y-value',
        ];

        $thumbprint = $this->invokePrivate($middleware, 'jwkThumbprint', [$jwk]);

        $this->assertIsString($thumbprint);
        $this->assertNotSame('', $thumbprint);
        $this->assertNull($this->invokePrivate($middleware, 'jwkThumbprint', [['kty' => 'RSA']]));

        $encoded = $this->invokePrivate($middleware, 'base64UrlEncode', ['payload.sample']);
        $decoded = $this->invokePrivate($middleware, 'base64UrlDecode', [$encoded]);

        $this->assertSame('payload.sample', $decoded);
    }

    public function test_get_jwks_uses_http_endpoint_and_cache(): void
    {
        Cache::flush();
        Http::fake([
            '*' => Http::response(['keys' => [['kty' => 'RSA']]], 200),
        ]);

        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'myrealm',
            'keycloak.jwks_ttl' => 600,
        ]);

        $middleware = new KeycloakJwtMiddleware;

        $first = $this->invokePrivate($middleware, 'getJwks');
        $second = $this->invokePrivate($middleware, 'getJwks');

        $this->assertIsArray($first);
        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_is_valid_dpop_returns_true_for_matching_header_claims_and_request(): void
    {
        $middleware = new KeycloakJwtMiddleware;

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => 'abc123',
            'y' => 'def456',
        ];

        $jkt = $this->invokePrivate($middleware, 'jwkThumbprint', [$jwk]);

        $header = $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode(['typ' => 'dpop+jwt', 'jwk' => $jwk], JSON_THROW_ON_ERROR)]);
        $payload = $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode([
            'htu' => 'http://localhost/api/protected',
            'htm' => 'POST',
        ], JSON_THROW_ON_ERROR)]);

        $request = Request::create('http://localhost/api/protected', 'POST');
        $request->headers->set('DPoP', $header . '.' . $payload . '.signature');

        $result = $this->invokePrivate($middleware, 'isValidDpop', [$request, ['cnf' => ['jkt' => $jkt]]]);

        $this->assertTrue($result);
    }

    public function test_is_valid_dpop_returns_false_when_dpop_header_is_missing(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('http://localhost/api/protected', 'GET');

        $result = $this->invokePrivate($middleware, 'isValidDpop', [$request, ['cnf' => ['jkt' => 'abc']]]);

        $this->assertFalse($result);
    }

    public function test_private_guard_helpers_cover_additional_branches(): void
    {
        $middleware = new KeycloakJwtMiddleware;

        config(['keycloak.require_dpop' => true]);
        $this->assertTrue($this->invokePrivate($middleware, 'shouldRequireDpop', [['typ' => 'DPoP']]));
        $this->assertFalse($this->invokePrivate($middleware, 'shouldRequireDpop', [['typ' => 'Bearer']]));

        config(['keycloak.issuer' => 'https://issuer.test/realms/demo']);
        $this->assertTrue($this->invokePrivate($middleware, 'isValidIssuer', [['iss' => 'https://issuer.test/realms/demo']]));
        $this->assertFalse($this->invokePrivate($middleware, 'isValidIssuer', [['iss' => 'https://other.test/realm']]));

        $this->assertSame('keycloak.jwks', $this->invokePrivate($middleware, 'jwksCacheKey'));
    }

    public function test_decode_claims_returns_null_with_null_jwks_or_invalid_token(): void
    {
        $middleware = new KeycloakJwtMiddleware;

        $nullJwks = $this->invokePrivate($middleware, 'decodeClaims', ['token', null]);
        $invalidToken = $this->invokePrivate($middleware, 'decodeClaims', ['not-a-jwt', ['keys' => []]]);

        $this->assertNull($nullJwks);
        $this->assertNull($invalidToken);
    }

    public function test_handle_returns_401_for_missing_or_invalid_authorization_header(): void
    {
        $this->app->instance('env', 'production');

        $middleware = new KeycloakJwtMiddleware;

        $requestMissing = Request::create('/api/resource', 'GET');
        $responseMissing = $middleware->handle($requestMissing, fn () => response('ok', 200));
        $this->assertSame(401, $responseMissing->getStatusCode());

        $requestInvalid = Request::create('/api/resource', 'GET');
        $requestInvalid->headers->set('Authorization', 'Token abc');
        $responseInvalid = $middleware->handle($requestInvalid, fn () => response('ok', 200));
        $this->assertSame(401, $responseInvalid->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    public function test_handle_returns_401_when_token_decode_or_claim_validation_fails(): void
    {
        $this->app->instance('env', 'production');

        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'api-gateway',
            'keycloak.issuer' => 'http://keycloak.test/realms/demo',
            'keycloak.jwks_ttl' => 60,
        ]);

        Http::fake([
            '*' => Http::response(['keys' => []], 200),
        ]);

        $middleware = new KeycloakJwtMiddleware;

        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'Bearer not-a-jwt');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(401, $response->getStatusCode());

        $this->app->instance('env', 'testing');
    }

    public function test_pact_bypass_helpers_cover_env_and_header_paths(): void
    {
        $middleware = new KeycloakJwtMiddleware;

        $this->app->instance('env', 'local');
        putenv('PACT_BYPASS_AUTH=true');
        $_ENV['PACT_BYPASS_AUTH'] = 'true';

        $requestPathMatch = Request::create('/api/_pact/state', 'GET');
        $requestPathNoMatch = Request::create('/api/products', 'GET');

        $this->assertIsBool($this->invokePrivate($middleware, 'shouldBypassAuthForPact', [$requestPathMatch]));
        $this->assertIsBool($this->invokePrivate($middleware, 'shouldBypassAuthForPact', [$requestPathNoMatch]));

        putenv('PACT_BYPASS_AUTH=false');
        $_ENV['PACT_BYPASS_AUTH'] = 'false';
        putenv('PACT_BYPASS_HEADER_SECRET=secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'secret';

        $headerRequest = Request::create('/api/any', 'GET');
        $headerRequest->headers->set('X-Pact-Request', 'true');
        $headerRequest->headers->set('X-Pact-Secret', 'secret');

        $this->assertIsBool($this->invokePrivate($middleware, 'shouldBypassAuthForPact', [$headerRequest]));

        $this->app->instance('env', 'testing');
    }

    // -----------------------------------------------------------------------
    // handle() bypass path (line 26)
    // -----------------------------------------------------------------------

    public function test_handle_calls_next_when_running_unit_tests(): void
    {
        // env stays 'testing' → shouldBypassForTests() = true → line 26 hit
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('ok', 200);
        });
        $this->assertTrue($called);
        $this->assertSame(200, $response->getStatusCode());
    }

    // -----------------------------------------------------------------------
    // handle() DPoP prefix + empty token (lines 38, 44)
    // -----------------------------------------------------------------------

    public function test_handle_returns_401_for_dpop_prefix(): void
    {
        $this->app->instance('env', 'production');
        Cache::flush();
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.jwks_ttl' => 60,
        ]);
        Http::fake(['*' => Http::response(['keys' => []], 200)]);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'DPoP sometoken');
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(401, $response->getStatusCode());
        $this->app->instance('env', 'testing');
    }

    public function test_handle_returns_401_for_empty_bearer_token(): void
    {
        $this->app->instance('env', 'production');
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'Bearer    ');
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(401, $response->getStatusCode());
        $this->app->instance('env', 'testing');
    }

    // -----------------------------------------------------------------------
    // handle() full JWT flow: invalid issuer/audience, success (52-53, 56, 62, 64)
    // and decodeClaims/decodeClaimsWithCachedJwks success (lines 244, 261-266)
    // -----------------------------------------------------------------------

    public function test_handle_returns_401_when_issuer_invalid(): void
    {
        $this->app->instance('env', 'production');
        Cache::flush();
        [$token, $jwks] = $this->makeTestJwtAndJwks([
            'iss' => 'https://wrong-issuer/realms/demo',
            'aud' => 'api-test-client',
        ]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'api-test-client',
            'keycloak.issuer' => 'https://correct-issuer/realms/demo',
            'keycloak.jwks_ttl' => 60,
            'keycloak.require_dpop' => false,
        ]);
        Http::fake(['*' => Http::response($jwks, 200)]);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(401, $response->getStatusCode());
        $this->app->instance('env', 'testing');
    }

    public function test_handle_passes_with_valid_claims_and_sets_token(): void
    {
        $this->app->instance('env', 'production');
        Cache::flush();
        [$token, $jwks] = $this->makeTestJwtAndJwks([
            'iss' => 'https://correct-issuer/realms/demo',
            'aud' => 'api-test-client',
        ]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'api-test-client',
            'keycloak.issuer' => 'https://correct-issuer/realms/demo',
            'keycloak.jwks_ttl' => 60,
            'keycloak.require_dpop' => false,
        ]);
        Http::fake(['*' => Http::response($jwks, 200)]);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($request->attributes->get('token'));
        $this->app->instance('env', 'testing');
    }

    public function test_handle_returns_401_when_dpop_required_but_dpop_header_missing(): void
    {
        $this->app->instance('env', 'production');
        Cache::flush();
        [$token, $jwks] = $this->makeTestJwtAndJwks([
            'iss' => 'https://correct-issuer/realms/demo',
            'aud' => 'api-test-client',
            'typ' => 'DPoP',
        ]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.client_id' => 'api-test-client',
            'keycloak.issuer' => 'https://correct-issuer/realms/demo',
            'keycloak.jwks_ttl' => 60,
            'keycloak.require_dpop' => true,
        ]);
        Http::fake(['*' => Http::response($jwks, 200)]);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/resource', 'GET');
        $request->headers->set('Authorization', 'DPoP ' . $token);
        // No DPoP header → isValidDpop returns false
        $response = $middleware->handle($request, fn () => response('ok', 200));
        $this->assertSame(401, $response->getStatusCode());
        $this->app->instance('env', 'testing');
    }

    // -----------------------------------------------------------------------
    // getJwks() error paths (lines 102-103, 107, 113)
    // -----------------------------------------------------------------------

    public function test_get_jwks_returns_null_when_http_throws_exception(): void
    {
        Cache::flush();
        Http::fake(['*' => function () {
            throw new \RuntimeException('Connection refused');
        }]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.jwks_ttl' => 60,
        ]);
        $middleware = new KeycloakJwtMiddleware;
        $result = $this->invokePrivate($middleware, 'getJwks');
        $this->assertNull($result);
    }

    public function test_get_jwks_returns_null_when_response_is_not_ok(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response([], 500)]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.jwks_ttl' => 60,
        ]);
        $middleware = new KeycloakJwtMiddleware;
        $result = $this->invokePrivate($middleware, 'getJwks');
        $this->assertNull($result);
    }

    public function test_get_jwks_returns_null_when_response_has_no_keys_field(): void
    {
        Cache::flush();
        Http::fake(['*' => Http::response(['data' => 'nope'], 200)]);
        config([
            'keycloak.base_url' => 'http://keycloak.test',
            'keycloak.realm' => 'demo',
            'keycloak.jwks_ttl' => 60,
        ]);
        $middleware = new KeycloakJwtMiddleware;
        $result = $this->invokePrivate($middleware, 'getJwks');
        $this->assertNull($result);
    }

    // -----------------------------------------------------------------------
    // isValidAudience() additional branches (lines 146, 149)
    // -----------------------------------------------------------------------

    public function test_is_valid_audience_returns_false_for_null_aud(): void
    {
        config(['keycloak.client_id' => 'api-gateway']);
        $middleware = new KeycloakJwtMiddleware;
        $this->assertFalse($this->invokePrivate($middleware, 'isValidAudience', [[]]));
    }

    public function test_is_valid_audience_handles_array_aud_account_with_azp(): void
    {
        config(['keycloak.client_id' => 'api-gateway']);
        $middleware = new KeycloakJwtMiddleware;
        $this->assertTrue($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => ['account', 'other'],
            'azp' => 'api-gateway',
        ]]));
        $this->assertFalse($this->invokePrivate($middleware, 'isValidAudience', [[
            'aud' => ['account', 'other'],
            'azp' => 'wrong-client',
        ]]));
    }

    // -----------------------------------------------------------------------
    // isValidDpop() branch coverage (lines 169, 174-175, 181, 188, 195, 201)
    // -----------------------------------------------------------------------

    public function test_is_valid_dpop_returns_false_when_wrong_part_count(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('http://localhost/api/resource', 'GET');
        $request->headers->set('DPoP', 'only.twoparts');
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, []]));
    }

    public function test_is_valid_dpop_returns_false_when_header_is_invalid_base64_json(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('http://localhost/api/resource', 'GET');
        // strtr maps '-_' to '+/' before base64_decode; '!!!' is not valid base64 JSON
        $request->headers->set('DPoP', '!!!invalid!!!.!!!invalid!!!.sig');
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, []]));
    }

    public function test_is_valid_dpop_returns_false_when_jwk_missing_from_header(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $enc = fn (array $d) => $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode($d)]);
        $request = Request::create('http://localhost/api/resource', 'GET');
        $request->headers->set('DPoP', $enc(['typ' => 'dpop+jwt']) . '.' . $enc(['htu' => 'x', 'htm' => 'GET']) . '.sig');
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, []]));
    }

    public function test_is_valid_dpop_returns_false_when_url_or_method_mismatch(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $jwk = ['kty' => 'EC', 'crv' => 'P-256', 'x' => 'abc', 'y' => 'def'];
        $enc = fn (array $d) => $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode($d)]);
        // Build DPoP with wrong htu
        $dpop = $enc(['jwk' => $jwk]) . '.' . $enc(['htu' => 'http://other-url/api', 'htm' => 'GET']) . '.sig';
        $request = Request::create('http://localhost/api/resource', 'GET');
        $request->headers->set('DPoP', $dpop);
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, []]));
    }

    public function test_is_valid_dpop_returns_false_when_cnf_jkt_missing(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $jwk = ['kty' => 'EC', 'crv' => 'P-256', 'x' => 'abc', 'y' => 'def'];
        $enc = fn (array $d) => $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode($d)]);
        $dpop = $enc(['jwk' => $jwk]) . '.' . $enc(['htu' => 'http://localhost/api/resource', 'htm' => 'GET']) . '.sig';
        $request = Request::create('http://localhost/api/resource', 'GET');
        $request->headers->set('DPoP', $dpop);
        // claims with no cnf.jkt
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, ['cnf' => []]]));
    }

    public function test_is_valid_dpop_returns_false_when_thumbprint_mismatch(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $jwk = ['kty' => 'EC', 'crv' => 'P-256', 'x' => 'abc', 'y' => 'def'];
        $enc = fn (array $d) => $this->invokePrivate($middleware, 'base64UrlEncode', [json_encode($d)]);
        $dpop = $enc(['jwk' => $jwk]) . '.' . $enc(['htu' => 'http://localhost/api/resource', 'htm' => 'GET']) . '.sig';
        $request = Request::create('http://localhost/api/resource', 'GET');
        $request->headers->set('DPoP', $dpop);
        // claims with wrong jkt value
        $this->assertFalse($this->invokePrivate($middleware, 'isValidDpop', [$request, ['cnf' => ['jkt' => 'wrong-thumbprint']]]));
    }

    // -----------------------------------------------------------------------
    // jwkThumbprint() missing x or y key (line 213)
    // -----------------------------------------------------------------------

    public function test_jwk_thumbprint_returns_null_when_x_or_y_missing(): void
    {
        $middleware = new KeycloakJwtMiddleware;
        $this->assertNull($this->invokePrivate($middleware, 'jwkThumbprint', [['kty' => 'EC', 'crv' => 'P-256', 'x' => 'abc']]));
        $this->assertNull($this->invokePrivate($middleware, 'jwkThumbprint', [['kty' => 'EC', 'crv' => 'P-256', 'y' => 'abc']]));
    }

    // -----------------------------------------------------------------------
    // hasValidPactSecret() (lines 282-291)
    // -----------------------------------------------------------------------

    public function test_has_valid_pact_secret_returns_true_when_no_secret_configured(): void
    {
        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET'], $_SERVER['PACT_BYPASS_HEADER_SECRET']);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/any', 'GET');
        $this->assertTrue($this->invokePrivate($middleware, 'hasValidPactSecret', [$request]));
    }

    public function test_has_valid_pact_secret_returns_false_when_wrong_secret(): void
    {
        putenv('PACT_BYPASS_HEADER_SECRET=correct-secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'correct-secret';
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/any', 'GET');
        $request->headers->set('X-Pact-Secret', 'wrong-secret');
        $this->assertFalse($this->invokePrivate($middleware, 'hasValidPactSecret', [$request]));
        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);
    }

    // -----------------------------------------------------------------------
    // shouldBypassAuthForPact() header path (lines 77-82)
    // -----------------------------------------------------------------------

    public function test_should_bypass_auth_for_pact_returns_true_via_header_when_secret_matches(): void
    {
        $this->app->instance('env', 'local');
        putenv('PACT_BYPASS_AUTH');
        unset($_ENV['PACT_BYPASS_AUTH'], $_SERVER['PACT_BYPASS_AUTH']);
        putenv('PACT_BYPASS_HEADER_SECRET=my-secret');
        $_ENV['PACT_BYPASS_HEADER_SECRET'] = 'my-secret';
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/any', 'GET');
        $request->headers->set('X-Pact-Request', 'true');
        $request->headers->set('X-Pact-Secret', 'my-secret');
        $this->assertTrue($this->invokePrivate($middleware, 'shouldBypassAuthForPact', [$request]));
        putenv('PACT_BYPASS_HEADER_SECRET');
        unset($_ENV['PACT_BYPASS_HEADER_SECRET']);
        $this->app->instance('env', 'testing');
    }

    public function test_should_bypass_auth_for_pact_returns_false_when_x_pact_request_header_absent(): void
    {
        $this->app->instance('env', 'local');
        putenv('PACT_BYPASS_AUTH');
        unset($_ENV['PACT_BYPASS_AUTH'], $_SERVER['PACT_BYPASS_AUTH']);
        $middleware = new KeycloakJwtMiddleware;
        $request = Request::create('/api/any', 'GET');
        // no X-Pact-Request header
        $this->assertFalse($this->invokePrivate($middleware, 'shouldBypassAuthForPact', [$request]));
        $this->app->instance('env', 'testing');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Generate a real RSA-signed JWT and matching JWKS structure for testing.
     *
     * @param  array<string,mixed>  $extraClaims
     * @return array{0: string, 1: array<string,mixed>}
     */
    private function makeTestJwtAndJwks(array $extraClaims = []): array
    {
        $keyResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $keyDetails = openssl_pkey_get_details($keyResource);
        $n = rtrim(strtr(base64_encode($keyDetails['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($keyDetails['rsa']['e']), '+/', '-_'), '=');
        $jwks = [
            'keys' => [[
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'n' => $n,
                'e' => $e,
                'kid' => 'test-kid',
            ]],
        ];
        $payload = array_merge([
            'exp' => time() + 3600,
            'iat' => time(),
        ], $extraClaims);
        $token = JWT::encode($payload, $keyResource, 'RS256', 'test-kid');

        return [$token, $jwks];
    }

    private function invokePrivate(object $instance, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($instance);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($instance, $arguments);
    }
}
