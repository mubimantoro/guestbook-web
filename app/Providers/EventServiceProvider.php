<?php

namespace App\Providers;

use App\Events\TamuRegistered;
use App\Listeners\SendNotificationToPIC;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TamuRegistered::class => [
            SendNotificationToPIC::class,
        ],
        // StatusPertemuanUpdated::class => [
        //     SendStatusUpdateToGuest::class,
        // ],
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
