<?php

namespace App\Providers;

use App\Events\StatusTamuUpdated;
use App\Events\TamuRegistered;
use App\Listeners\SendNotificationToPIC;
use App\Listeners\SendStatusUpdateToTamu;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TamuRegistered::class => [
            SendNotificationToPIC::class,
        ],
        StatusTamuUpdated::class => [
            SendStatusUpdateToTamu::class,
        ],
        // PenilaianSubmitted::class => [
        //     LogPenilaianSubmitted::class,
        // ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
