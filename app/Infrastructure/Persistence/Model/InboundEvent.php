<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @package App\Infrastructure\Persistence\Model
 */
class InboundEvent extends BaseModel
{
    protected $table = 'inbound_events';
    protected $guarded = [];
}