<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Martindevnow\Smartshelf\Engineering\Events\LowStockAlert::class => [
            \Martindevnow\Smartshelf\Engineering\Listeners\LogLowStockEvent::class,
//            \Martindevnow\Smartshelf\Engineering\Listeners\EmailLowStockNotification::class,
//            \Martindevnow\Smartshelf\Engineering\Listeners\SMSLowStockNotification::class,
        ],

        \Martindevnow\Smartshelf\Engineering\Events\InventoryOutOfStock::class => [
            \Martindevnow\Smartshelf\Engineering\Listeners\LogOutOfStockEvent::class,
//            \Martindevnow\Smartshelf\Engineering\Listeners\EmailInventoryOutOfStockNotification::class,
//            \Martindevnow\Smartshelf\Engineering\Listeners\SMSInventoryOutOfStockNotification::class,
        ],

//        \Martindevnow\Smartshelf\Engineering\Events\InventoryOutOfStockTime::class => [
//            \Martindevnow\Smartshelf\Engineering\Listeners\EmailTimedOOSNotification::class,
//            \Martindevnow\Smartshelf\Engineering\Listeners\SMSTimedOOSNotification::class,
//        ],

        \Martindevnow\Smartshelf\Engineering\Events\PusherWasRestocked::class => [
            \Martindevnow\Smartshelf\Engineering\Listeners\LogRestockedEvent::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
