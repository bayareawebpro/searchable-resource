<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Support\ServiceProvider;
use BayAreaWebPro\SearchableResource\Commands\MakeQueryCommand;
use BayAreaWebPro\SearchableResource\Commands\MakeSearchableCommand;

class SearchableResourceServiceProvider extends ServiceProvider
{
    public function boot()
    {
         $this->loadTranslationsFrom(__DIR__.'/../lang', 'searchable-resource');

        if ($this->app->runningInConsole()) {

            $this->commands([
                MakeQueryCommand::class,
                MakeSearchableCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('searchable-resource.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/searchable-resource'),
            ], 'lang');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'searchable-resource');
        $this->app->bind('searchable-resource', SearchableBuilder::class);
    }

    public function provides()
    {
        return ['searchable-resource'];
    }
}
