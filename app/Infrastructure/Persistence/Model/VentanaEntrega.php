<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentanaEntrega extends Model
{
    use HasFactory;

    protected $table = 'ventana_entrega';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'desde',
        'hasta',
    ];
    protected $casts = [
        'desde' => 'datetime',
        'hasta' => 'datetime',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];
}