<?php

namespace App\Providers;

use App\Services\Gateway\Base\BaseServiceContract;
use App\Services\Gateway\Master\Bank\BankService;
use App\Services\Gateway\Master\Bank\BankServiceContract;
use App\Services\Gateway\Rest\RestService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{

    public function boot()
    {

        $this->app->singleton(BaseServiceContract::class, function () {
            return new RestService(
                new Client([
                    'timeout' => Config::get('gateway.timeout'),
                    'connect_timeout' =>
                        Config::get('gateway.connect_timeout',
                            Config::get('gateway.timeout')
                        )
                ])
            );
        });

        $this->app->singleton(BankServiceContract::class, BankService::class);

    }

    public function register()
    {
        //
    }

    protected function prepareRequest(Request $request)
    {
        return $request;
    }
}
