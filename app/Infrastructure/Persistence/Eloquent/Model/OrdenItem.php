<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class OrdenItem extends Model
{
    protected $table = 'order_item';
    public $timestamps = true;

    protected $fillable = ['op_id', 'p_id', 'qty', 'price', 'final_price'];
}