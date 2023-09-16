<?php

namespace Huangkuan\LaravelTranslator;

use Huangkuan\LaravelTranslator\LaravelTranslator;
use Huangkuan\LaravelTranslator\Translator;
use Illuminate\Contracts\Support\DeferrableProvider;
use \Illuminate\Support\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('translator.php'),
        ], 'laravel-translator');
    }

    public function register()
    {
        $this->app->singleton('laravel-translator', function () {
            return new Translator();
        });
    }

    public function provides()
    {
        return ['laravel-translator', Translator::class];
    }
}
