<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class InboundEvent extends Model
{
    protected $table = 'inbound_events';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['event_id', 'event_name', 'occurred_on', 'payload'];
    public $timestamps = true;
}