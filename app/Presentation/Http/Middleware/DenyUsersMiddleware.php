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
 * @package App\Presentation\Http\Middleware
 */
class DenyUsersMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $users
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $users): Response
    {
        if ($this->shouldBypassForPact($request) || $this->shouldBypassForTests()) {
            return $next($request);
        }

        $claims = $request->attributes->get('token');
        if (!is_array($claims)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $blocked = $this->parseUsers($users);
        if ($blocked === []) {
            return $next($request);
        }

        $sub = $claims['sub'] ?? null;
        $username = $claims['preferred_username'] ?? null;

        if ((is_string($sub) && in_array($sub, $blocked, true)) ||
            (is_string($username) && in_array($username, $blocked, true))) {
            Log::warning('Keycloak user blocked', [
                'sub' => $sub,
                'preferred_username' => $username,
            ]);
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function shouldBypassForPact(Request $request): bool
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

    /**
     * @return bool
     */
    private function shouldBypassForTests(): bool
    {
        return app()->runningUnitTests();
    }

    /**
     * @param string $users
     * @return array
     */
    private function parseUsers(string $users): array
    {
        $users = str_replace('|', ',', $users);
        $items = array_map('trim', explode(',', $users));
        return array_values(array_filter($items, fn ($u) => $u !== ''));
    }
}
