<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;

class OrdenItem extends Model
{
    protected $table = 'order_item';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['op_id', 'p_id', 'sku', 'qty', 'price', 'final_price'];
    public $timestamps = true;
}