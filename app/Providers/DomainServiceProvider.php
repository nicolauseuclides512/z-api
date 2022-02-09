<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->app->singleton(
            'App\\Domain\\Contracts\\ReasonContract',
            'App\\Domain\\Services\\ReasonService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\DocumentCounterContract',
            'App\\Domain\\Services\\DocumentCounterService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\StockAdjustmentContract',
            'App\\Domain\\Services\\StockAdjustmentService'
        );

        $this->app->bind(
            'App\\Domain\\Contracts\\StockContract',
            'App\\Domain\\Services\\StockService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\SalesChannelContract',
            'App\\Domain\\Services\\SalesChannelService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\MySalesChannelContract',
            'App\\Domain\\Services\\MySalesChannelService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\SalesOrderContract',
            'App\\Domain\\Services\\SalesOrderService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\PaymentContract',
            'App\\Domain\\Services\\PaymentService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\ShipmentContract',
            'App\\Domain\\Services\\ShipmentService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\LazadaAPIConfigContract',
            'App\\Domain\\Services\\LazadaAPIConfigService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\LazadaItemAliasContract',
            'App\\Domain\\Services\\LazadaItemAliasService'
        );

        $this->app->singleton(
            'App\\Domain\\Contracts\\MarketplaceIntegrationLogContract',
            'App\\Domain\\Services\\MarketplaceIntegrationLogService'
        );

        $this->app->bind(
            'App\\Domain\\Contracts\\ItemRepository',
            'App\\Domain\\Repository\\EloquentItemRepository'
        );
    }
}



