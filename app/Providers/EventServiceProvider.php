<?php

namespace App\Providers;

use App\Events\StatusTamuUpdated;
use App\Events\TamuRegistered;
use App\Events\TamuRescheduled;
use App\Listeners\SendNotificationToAdmin;
use App\Listeners\SendNotificationToPIC;
use App\Listeners\SendRescheduleNotification;
use App\Listeners\SendStatusUpdateToTamu;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TamuRegistered::class => [
            SendNotificationToPIC::class,
            SendNotificationToAdmin::class,
        ],
        StatusTamuUpdated::class => [
            SendStatusUpdateToTamu::class,
        ],
        TamuRescheduled::class => [
            SendRescheduleNotification::class,
        ]
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
