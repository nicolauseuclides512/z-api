<?php

namespace App\Providers;

use App\Models\Contract\ItemContract;
use App\Models\Item;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        $this->app->singleton(
            ItemContract::class,
            Item::class
        );
    }

}
