<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model\Concerns;

use Illuminate\Support\Str;

/**
 * @trait HasUuid
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }
}
