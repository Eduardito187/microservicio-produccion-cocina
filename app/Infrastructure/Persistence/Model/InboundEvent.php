<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class InboundEvent
 * @package App\Infrastructure\Persistence\Model
 */
class InboundEvent extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'inbound_events';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'schema_version' => 'integer',
    ];
}
