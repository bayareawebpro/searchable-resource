<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use BayAreaWebPro\SearchableResource\Commands\MakeQueryCommand;
use BayAreaWebPro\SearchableResource\Commands\MakeSearchableCommand;
use Illuminate\Support\ServiceProvider;

class SearchableResourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
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

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'searchable-resource');
        $this->app->bind('searchable-resource', SearchableBuilder::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['searchable-resource'];
    }
}
