<?php

namespace App\Infrastructure\Persistence\Model;

class Estacion extends BaseModel
{
    protected $table = 'estacion';
    protected $guarded = [];

    public function batches()
    {
        return $this->hasMany(ProduccionBatch::class, 'estacion_id');
    }
}