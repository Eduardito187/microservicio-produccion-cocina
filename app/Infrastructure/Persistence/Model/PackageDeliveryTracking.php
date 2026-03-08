<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class PackageDeliveryTracking
 */
class PackageDeliveryTracking extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'package_delivery_tracking';

    /**
     * @var mixed
     */
    protected $guarded = [];
}
