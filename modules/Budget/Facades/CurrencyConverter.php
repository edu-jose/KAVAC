<?php

namespace Modules\Budget\Facades;

use Illuminate\Support\Facades\Facade;

class CurrencyConverter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Modules\Budget\Services\CurrencyConverterService::class;
    }
}
