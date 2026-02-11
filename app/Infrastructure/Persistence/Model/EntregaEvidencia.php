<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

/**
 * @class EntregaEvidencia
 * @package App\Infrastructure\Persistence\Model
 */
class EntregaEvidencia extends BaseModel
{
    /**
     * @var mixed
     */
    protected $table = 'entrega_evidencia';
    /**
     * @var mixed
     */
    protected $guarded = [];

    protected $casts = [
        'geo' => 'array',
        'payload' => 'array',
    ];
}
