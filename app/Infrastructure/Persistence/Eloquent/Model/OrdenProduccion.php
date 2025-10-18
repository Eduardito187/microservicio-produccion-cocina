<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use App\Infrastructure\Persistence\Eloquent\Model\ProduccionBatch;
use App\Infrastructure\Persistence\Eloquent\Model\OrdenItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
    protected $table = 'orden_produccion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['fecha', 'sucursal_id', 'estado'];
    public $timestamps = true;

    /**
     * @return HasMany
     */
    public function items() : HasMany
    {
        return $this->hasMany(OrdenItem::class, 'op_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function batches() : HasMany
    {
        return $this->hasMany(ProduccionBatch::class, 'op_id', 'id');
    }
}