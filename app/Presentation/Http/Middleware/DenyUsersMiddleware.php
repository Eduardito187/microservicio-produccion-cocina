<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @class DenyUsersMiddleware
 */
class DenyUsersMiddleware
{
    public function handle(Request $request, Closure $next, string $users = ''): Response
    {
        if ($this->shouldBypassForPact($request) || $this->shouldBypassForTests()) {
            return $next($request);
        }

        $claims = $request->attributes->get('token');
        if (! is_array($claims)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $blocked = $this->resolveBlockedUsers($users);
        if ($blocked === []) {
            return $next($request);
        }

        $sub = $claims['sub'] ?? null;
        $username = $claims['preferred_username'] ?? null;

        if ((is_string($sub) && in_array($sub, $blocked, true)) ||
            (is_string($username) && in_array($username, $blocked, true))) {
            Log::warning('Usuario de Keycloak bloqueado', [
                'sub' => $sub,
                'preferred_username' => $username,
            ]);

            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    private function shouldBypassForPact(Request $request): bool
    {
        if (! $this->isPactBypassEnvironment()) {
            return false;
        }

        if ($this->readBoolEnv('PACT_BYPASS_AUTH', false)) {
            return $request->is('api/_pact/*');
        }

        $pactHeader = $request->header('X-Pact-Request');
        if (is_string($pactHeader) && strtolower($pactHeader) === 'true' && $this->hasValidPactSecret($request)) {
            return true;
        }

        return false;
    }

    private function shouldBypassForTests(): bool
    {
        return app()->runningUnitTests();
    }

    private function parseUsers(string $users): array
    {
        $users = str_replace('|', ',', $users);
        $items = array_map('trim', explode(',', $users));

        return array_values(array_filter($items, fn ($u) => $u !== ''));
    }

    private function resolveBlockedUsers(string $users): array
    {
        $fromRoute = $this->parseUsers($users);
        if ($fromRoute !== []) {
            return $fromRoute;
        }

        $fromConfig = config('keycloak.blocked_users', []);
        if (! is_array($fromConfig)) {
            return [];
        }

        $normalized = array_values(array_filter(array_map(static function ($value) {
            return is_string($value) ? trim($value) : '';
        }, $fromConfig), static fn (string $value) => $value !== ''));

        return array_values(array_unique($normalized));
    }

    private function isPactBypassEnvironment(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    private function hasValidPactSecret(Request $request): bool
    {
        $expected = $this->readStringEnv('PACT_BYPASS_HEADER_SECRET', '');
        if ($expected === '') {
            return true;
        }

        $provided = $request->header('X-Pact-Secret', '');

        return is_string($provided) && hash_equals($expected, $provided);
    }

    private function readStringEnv(string $key, string $default = ''): string
    {
        $value = getenv($key);

        return is_string($value) ? $value : $default;
    }

    private function readBoolEnv(string $key, bool $default = false): bool
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return (bool) $value;
    }
}
