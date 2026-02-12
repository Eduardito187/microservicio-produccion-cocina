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
 * @package App\Presentation\Http\Middleware
 */
class RequireRoleMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($this->shouldBypassForPact($request) || $this->shouldBypassForTests()) {
            return $next($request);
        }

        $claims = $request->attributes->get('token');
        if (!is_array($claims)) {
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

        Log::warning('Keycloak role denied', [
            'required' => $required,
            'available' => $available,
            'sub' => $claims['sub'] ?? null,
        ]);

        return response()->json(['message' => 'Forbidden'], 403);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function shouldBypassForPact(Request $request): bool
    {
        if (app()->environment(['local', 'testing']) && (bool) env('PACT_BYPASS_AUTH', false)) {
            return $request->is('api/_pact/*');
        }

        $pactHeader = $request->header('X-Pact-Request');
        if (app()->environment(['local', 'testing']) && is_string($pactHeader) && strtolower($pactHeader) === 'true') {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function shouldBypassForTests(): bool
    {
        return app()->runningUnitTests();
    }

    /**
     * @param array $roles
     * @return array
     */
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

    /**
     * @param array $claims
     * @return array
     */
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

    /**
     * @param mixed $value
     * @return array
     */
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
}
