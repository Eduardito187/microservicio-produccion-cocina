<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Porcion extends Model
{
    use HasFactory;

    protected $table = 'porcion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'nombre',
        'peso_gr',
    ];
    protected $casts = [
        'peso_gr'    => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}