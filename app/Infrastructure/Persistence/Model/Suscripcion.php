<?php

namespace App\Infrastructure\Persistence\Model;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Persistence\Model\Paciente;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripcion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'paciente_id',
        'estado',
    ];
    protected $casts = [
        'paciente_id' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /** 
     * @return BelongsTo
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}