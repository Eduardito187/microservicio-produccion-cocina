<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Persistence\Model;

class EntregaEvidencia extends BaseModel
{
    protected $table = 'entrega_evidencia';
    protected $guarded = [];

    protected $casts = [
        'geo' => 'array',
        'payload' => 'array',
    ];
}
