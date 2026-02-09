<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

class EventStore extends BaseModel
{
    protected $table = 'event_store';
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'schema_version' => 'integer',
    ];
}
