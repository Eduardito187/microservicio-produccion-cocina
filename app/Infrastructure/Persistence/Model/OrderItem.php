<?php

namespace App\Infrastructure\Persistence\Model;

class OrderItem extends BaseModel
{
    protected $table = 'order_item';
    protected $guarded = [];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'op_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'p_id');
    }
}