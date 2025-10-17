<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
    protected $table = 'orden_produccion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'fecha', 'sucursal_id', 'estado'];
}