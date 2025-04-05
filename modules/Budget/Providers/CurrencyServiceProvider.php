<?php

namespace Modules\Budget\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Budget\Services\CurrencyConverterService;
use Modules\Budget\Services\CurrencyHistoryService;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrencyConverterService::class, function ($app) {
            return new CurrencyConverterService();
        });

        $this->app->singleton(CurrencyHistoryService::class, function ($app) {
            return new CurrencyHistoryService();
        });
    }

    public function boot()
    {
        // Configuraciones adicionales si son necesarias
    }
}
