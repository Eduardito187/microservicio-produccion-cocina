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
 * @class RequireRoleMiddleware
 */
class RequireRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($this->shouldBypassForPact($request) || $this->shouldBypassForTests()) {
            return $next($request);
        }

        $claims = $request->attributes->get('token');
        if (! is_array($claims)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $required = $this->parseRoles($roles);
        if ($required === []) {
            return $next($request);
        }

        $available = $this->extractRoles($claims);
        foreach ($required as $role) {
            if (in_array($role, $available, true)) {
                return $next($request);
            }
        }

        Log::warning('Rol de Keycloak denegado', [
            'required' => $required,
            'available' => $available,
            'sub' => $claims['sub'] ?? null,
        ]);

        return response()->json(['message' => 'Forbidden'], 403);
    }

    private function shouldBypassForPact(Request $request): bool
    {
        if (! $this->isPactBypassEnvironment()) {
            return false;
        }

        if ((bool) env('PACT_BYPASS_AUTH', false)) {
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

    private function parseRoles(array $roles): array
    {
        $items = [];
        foreach ($roles as $chunk) {
            $chunk = str_replace('|', ',', $chunk);
            $parts = array_map('trim', explode(',', $chunk));
            foreach ($parts as $part) {
                if ($part !== '') {
                    $items[] = $part;
                }
            }
        }

        return array_values(array_unique($items));
    }

    private function extractRoles(array $claims): array
    {
        $roles = [];

        $realmAccess = $this->toArray($claims['realm_access'] ?? []);
        $realmRoles = $realmAccess['roles'] ?? [];
        if (is_array($realmRoles)) {
            $roles = array_merge($roles, $realmRoles);
        }

        $clientId = config('keycloak.client_id');
        $resourceAccess = $this->toArray($claims['resource_access'] ?? []);
        $clientAccess = $this->toArray($resourceAccess[$clientId] ?? []);
        $clientRoles = $clientAccess['roles'] ?? [];
        if (is_array($clientRoles)) {
            $roles = array_merge($roles, $clientRoles);
        }

        return array_values(array_unique($roles));
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            /** @var array $arrayValue */
            $arrayValue = (array) $value;

            return $arrayValue;
        }

        return [];
    }

    private function isPactBypassEnvironment(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    private function hasValidPactSecret(Request $request): bool
    {
        $expected = (string) env('PACT_BYPASS_HEADER_SECRET', '');
        if ($expected === '') {
            return true;
        }

        $provided = $request->header('X-Pact-Secret', '');

        return is_string($provided) && hash_equals($expected, $provided);
    }
}
