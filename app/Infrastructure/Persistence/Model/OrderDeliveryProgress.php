<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class OrderDeliveryProgress
 */
class OrderDeliveryProgress extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'order_delivery_progress';

    /**
     * @var mixed
     */
    protected $guarded = [];
}
