<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\StockUpdated' => [
//            'App\Listeners\UpdateLazadaStock',
        
            //TODO more listener for another marketplace
        ],
//        'App\Events\LazadaItemUpdated' => [
//            'App\Listeners\UpdateLazadaPrice',
//        ],
    ];
}
