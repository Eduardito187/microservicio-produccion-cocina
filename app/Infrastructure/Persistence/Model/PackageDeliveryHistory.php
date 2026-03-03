<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class PackageDeliveryHistory
 * @package App\Infrastructure\Persistence\Model
 */
class PackageDeliveryHistory extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'package_delivery_history';

    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'evidence' => 'array',
        'payload' => 'array',
    ];
}
