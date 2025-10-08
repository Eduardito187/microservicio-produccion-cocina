<?php

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class Outbox extends Model
{
  protected $table = 'outbox';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['id', 'event_name', 'aggregate_id', 'payload', 'occurred_on', 'published_at'];
  protected $casts = ['payload' => 'array', 'occurred_on'=>'datetime', 'published_at'=>'datetime'];
}