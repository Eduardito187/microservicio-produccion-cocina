<?php

namespace App\Jobs;

use App\Infrastructure\Persistence\Eloquent\Model\Outbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Application\Shared\EventBus;
use Illuminate\Bus\Queueable;
use DateTimeImmutable;

class PublishOutbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * @param EventBus $bus
     * @return void
     */
    public function handle(EventBus $bus): void
    {
        Outbox::whereNull('published_at')->orderBy('occurred_on')->limit(100)->get()->each(function (Outbox $row) use ($bus) {
            $bus->publish(
                $row->id,
                $row->event_name,
                $row->payload,
                new DateTimeImmutable($row->occurred_on->format(DATE_ATOM))
            );

            $row->forceFill(['published_at' => now()])->save();
        });
    }
}