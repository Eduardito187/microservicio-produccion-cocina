<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['sku', 'price', 'special_price'];
    public $timestamps = true;
}