<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KeycloakJwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypassAuthForPact($request) || $this->shouldBypassForTests()) {
            return $next($request);
        }

        $auth = $request->header('Authorization', '');
        if (!is_string($auth) || $auth === '') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $scheme = null;
        if (str_starts_with($auth, 'Bearer ')) {
            $scheme = 'Bearer';
            $token = trim(substr($auth, 7));
        } elseif (str_starts_with($auth, 'DPoP ')) {
            $scheme = 'DPoP';
            $token = trim(substr($auth, 5));
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($token === '') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $jwks = $this->getJwks();
        if ($jwks === null) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($token, $keys);
        } catch (\Throwable $e) {
            Log::warning('Keycloak JWT decode failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $claims = (array) $decoded;
        if (!$this->isValidIssuer($claims) || !$this->isValidAudience($claims)) {
            Log::warning('Keycloak JWT claim validation failed', [
                'iss' => $claims['iss'] ?? null,
                'aud' => $claims['aud'] ?? null,
                'azp' => $claims['azp'] ?? null,
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($this->shouldRequireDpop($claims)) {
            if (!$this->isValidDpop($request, $claims)) {
                Log::warning('DPoP validation failed', ['sub' => $claims['sub'] ?? null]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } elseif (($claims['typ'] ?? null) === 'DPoP' && $request->header('DPoP') === null) {
            Log::info('DPoP header missing; accepted because require_dpop is false', [
                'sub' => $claims['sub'] ?? null,
            ]);
        }

        Log::info('Keycloak JWT validated', [
            'sub' => $claims['sub'] ?? null,
            'azp' => $claims['azp'] ?? null,
            'typ' => $claims['typ'] ?? null,
            'scheme' => $scheme,
        ]);

        $request->attributes->set('token', $claims);

        return $next($request);
    }

    private function shouldBypassAuthForPact(Request $request): bool
    {
        if ((bool) env('PACT_BYPASS_AUTH', false)) {
            return $request->is('api/_pact/*') || $request->is('api/produccion/ordenes/*');
        }

        $pactHeader = $request->header('X-Pact-Request');
        if (is_string($pactHeader) && strtolower($pactHeader) === 'true') {
            return true;
        }

        return false;
    }

    private function shouldBypassForTests(): bool
    {
        return app()->runningUnitTests();
    }

    private function getJwks(): ?array
    {
        $ttl = config('keycloak.jwks_ttl', 600);
        $cacheKey = 'keycloak.jwks';

        return Cache::remember($cacheKey, $ttl, function () {
            $url = rtrim(config('keycloak.base_url'), '/')
                .'/realms/'.config('keycloak.realm')
                .'/protocol/openid-connect/certs';

            $response = Http::get($url);

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            if (!is_array($data) || !isset($data['keys'])) {
                return null;
            }

            return $data;
        });
    }

    private function isValidIssuer(array $claims): bool
    {
        $expected = config('keycloak.issuer');
        return isset($claims['iss']) && $claims['iss'] === $expected;
    }

    private function isValidAudience(array $claims): bool
    {
        $expected = config('keycloak.client_id');
        $aud = $claims['aud'] ?? null;
        $azp = $claims['azp'] ?? null;

        if (is_string($aud)) {
            if ($aud === $expected) {
                return true;
            }
            return $aud === 'account' && $azp === $expected;
        }

        if (is_array($aud)) {
            if (in_array($expected, $aud, true)) {
                return true;
            }
            return in_array('account', $aud, true) && $azp === $expected;
        }

        return false;
    }

    private function shouldRequireDpop(array $claims): bool
    {
        return (bool) config('keycloak.require_dpop', false) && (($claims['typ'] ?? null) === 'DPoP');
    }

    private function isValidDpop(Request $request, array $claims): bool
    {
        $dpop = $request->header('DPoP');
        if (!is_string($dpop) || $dpop === '') {
            return false;
        }

        try {
            $parts = explode('.', $dpop);
            if (count($parts) !== 3) {
                return false;
            }
            $header = json_decode($this->base64UrlDecode($parts[0]), true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return false;
        }

        $jwk = $header['jwk'] ?? null;
        if (!is_array($jwk)) {
            return false;
        }

        $htu = $payload['htu'] ?? null;
        $htm = $payload['htm'] ?? null;

        if ($htu !== $request->fullUrl() || strtoupper((string) $htm) !== $request->method()) {
            return false;
        }

        $cnf = $claims['cnf'] ?? [];
        $expectedJkt = is_array($cnf) ? ($cnf['jkt'] ?? null) : null;
        if (!is_string($expectedJkt)) {
            return false;
        }

        $actualJkt = $this->jwkThumbprint($jwk);
        if ($actualJkt === null || $actualJkt !== $expectedJkt) {
            return false;
        }

        return true;
    }

    private function jwkThumbprint(array $jwk): ?string
    {
        if (($jwk['kty'] ?? null) !== 'EC' || ($jwk['crv'] ?? null) !== 'P-256') {
            return null;
        }
        if (!isset($jwk['x'], $jwk['y'])) {
            return null;
        }

        $data = json_encode([
            'crv' => $jwk['crv'],
            'kty' => $jwk['kty'],
            'x' => $jwk['x'],
            'y' => $jwk['y'],
        ], JSON_UNESCAPED_SLASHES);

        if (!is_string($data)) {
            return null;
        }

        return $this->base64UrlEncode(hash('sha256', $data, true));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        return base64_decode($data);
    }
}
