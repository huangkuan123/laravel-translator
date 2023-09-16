<?php

namespace Huangkuan\LaravelTranslator;

use Illuminate\Support\Facades\Facade;

class LaravelTranslator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-translator';
    }
}
