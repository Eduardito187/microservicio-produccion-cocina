<?php

namespace App\Providers;

use App\Infrastructure\EventBus\HttpEventBus;
use Illuminate\Support\ServiceProvider;
use App\Application\Shared\EventBus;

class EventBusServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(EventBus::class, fn() => new HttpEventBus());
    }
}