<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;

/**
 * @class RefreshController
 * @package App\Presentation\Http\Controllers
 */
class RefreshController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $baseUrl = rtrim(config('keycloak.base_url'), '/');
        $realm = config('keycloak.realm');
        $clientId = config('keycloak.client_id');
        $clientSecret = config('keycloak.client_secret');

        $payload = [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'refresh_token' => $data['refresh_token'],
        ];

        if (!empty($clientSecret)) {
            $payload['client_secret'] = $clientSecret;
        }

        $tokenUrl = $baseUrl.'/realms/'.$realm.'/protocol/openid-connect/token';
        $dpop = $this->buildDpopProof($tokenUrl, 'POST');

        $response = Http::asForm()->withHeaders([
            'DPoP' => $dpop,
        ])->post($tokenUrl, $payload);

        $body = $response->json();

        if ($response->ok()) {
            Log::info('Keycloak refresh success', [
                'realm' => $realm,
                'client_id' => $clientId,
            ]);
        } else {
            Log::warning('Keycloak refresh failed', [
                'realm' => $realm,
                'client_id' => $clientId,
                'status' => $response->status(),
                'error' => $body['error'] ?? null,
                'error_description' => $body['error_description'] ?? null,
            ]);
        }

        return response()->json($body, $response->status());
    }

    /**
     * @param string $url
     * @param string $method
     * @return string
     */
    private function buildDpopProof(string $url, string $method): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $details = openssl_pkey_get_details($key);
        $privateKey = null;
        openssl_pkey_export($key, $privateKey);

        $x = $details['ec']['x'] ?? null;
        $y = $details['ec']['y'] ?? null;

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => $this->base64UrlEncode($x ?: ''),
            'y' => $this->base64UrlEncode($y ?: ''),
        ];

        if (!is_string($privateKey) || $privateKey === '') {
            throw new \RuntimeException('Unable to export DPoP private key');
        }

        $payload = [
            'htu' => $url,
            'htm' => strtoupper($method),
            'iat' => time(),
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, $privateKey, 'ES256', null, [
            'typ' => 'dpop+jwt',
            'jwk' => $jwk,
        ]);
    }

    /**
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
