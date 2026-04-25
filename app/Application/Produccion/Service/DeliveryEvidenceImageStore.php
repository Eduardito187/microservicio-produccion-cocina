<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Produccion\Service;

use Illuminate\Support\Facades\Storage;

/**
 * Convierte una cadena base64 en un archivo de imagen guardado en disco público
 * y devuelve la URL accesible externamente.
 *
 * @class DeliveryEvidenceImageStore
 */
class DeliveryEvidenceImageStore
{
    public function store(string $packageId, string $eventId, string $base64): string
    {
        $ext = 'png';
        $data = $base64;

        if (str_starts_with($base64, 'data:')) {
            [$header, $data] = explode(',', $base64, 2);
            if (preg_match('/data:image\/(\w+);base64/', $header, $m)) {
                $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
            }
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false || strlen($decoded) === 0) {
            return '';
        }

        $path = "evidencias/{$packageId}/{$eventId}.{$ext}";
        Storage::disk('public')->put($path, $decoded);

        return Storage::disk('public')->url($path);
    }

    public function isBase64Image(mixed $value): bool
    {
        return is_string($value) && (
            str_starts_with($value, 'data:image/') ||
            (strlen($value) > 100 && ! str_starts_with($value, 'http'))
        );
    }
}
