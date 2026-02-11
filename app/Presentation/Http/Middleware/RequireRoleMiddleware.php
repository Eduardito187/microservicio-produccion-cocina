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
     * @param string $roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $roles): Response
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
            return $request->is('api/_pact/*') || $request->is('api/produccion/ordenes/*');
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
     * @param string $roles
     * @return array
     */
    private function parseRoles(string $roles): array
    {
        $roles = str_replace('|', ',', $roles);
        $items = array_map('trim', explode(',', $roles));
        return array_values(array_filter($items, fn ($r) => $r !== ''));
    }

    /**
     * @param array $claims
     * @return array
     */
    private function extractRoles(array $claims): array
    {
        $roles = [];

        $realmRoles = $claims['realm_access']['roles'] ?? [];
        if (is_array($realmRoles)) {
            $roles = array_merge($roles, $realmRoles);
        }

        $clientId = config('keycloak.client_id');
        $clientRoles = $claims['resource_access'][$clientId]['roles'] ?? [];
        if (is_array($clientRoles)) {
            $roles = array_merge($roles, $clientRoles);
        }

        return array_values(array_unique($roles));
    }
}
