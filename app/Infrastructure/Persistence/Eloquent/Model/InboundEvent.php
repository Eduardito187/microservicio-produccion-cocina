<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class InboundEvent extends Model
{
    protected $table = 'inbound_events';
    public $timestamps = true;

    protected $fillable = [
        'event_id','event_name','occurred_on','payload',
    ];
}