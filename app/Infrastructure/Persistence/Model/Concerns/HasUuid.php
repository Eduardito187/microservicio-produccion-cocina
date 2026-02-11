<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model\Concerns;

use Illuminate\Support\Str;

/**
 * @trait HasUuid
 * @package App\Infrastructure\Persistence\Model\Concerns
 */
trait HasUuid
{
    /**
     * @return void
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (!$model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }
}
