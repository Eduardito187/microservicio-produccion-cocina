<?php

namespace App\Infrastructure\Persistence\Model;

class Porcion extends BaseModel
{
    protected $table = 'porcion';
    protected $guarded = [];

    public function batches()
    {
        return $this->hasMany(ProduccionBatch::class, 'porcion_id');
    }
}