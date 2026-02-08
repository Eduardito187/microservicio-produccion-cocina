<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class Outbox extends BaseModel
{
    protected $table = 'outbox';
    protected $guarded = [];
    protected $casts = [
        'payload' => 'array',
        'occurred_on' => 'datetime',
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'schema_version' => 'integer',
    ];
}
