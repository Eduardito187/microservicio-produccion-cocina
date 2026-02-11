<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class EventStore
 * @package App\Infrastructure\Persistence\Model
 */
class EventStore extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'event_store';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'schema_version' => 'integer',
    ];
}
