<?php

namespace App\Infrastructure\Persistence\Model;

class Product extends BaseModel
{
    protected $table = 'products';
    protected $guarded = [];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'p_id');
    }

    public function despachoItems()
    {
        return $this->hasMany(ItemDespacho::class, 'product_id');
    }

    public function batches()
    {
        return $this->hasMany(ProduccionBatch::class, 'p_id');
    }
}