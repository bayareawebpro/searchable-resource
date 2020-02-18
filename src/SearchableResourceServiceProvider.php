<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use BayAreaWebPro\SearchableResource\Commands\MakeQueryCommand;
use Illuminate\Support\ServiceProvider;

class SearchableResourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'query-resource');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'query-resource');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('query-resource.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/query-resource'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/query-resource'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/query-resource'),
            ], 'lang');*/

            // Registering package commands.
             $this->commands([
                 MakeQueryCommand::class
             ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'searchable-resource');

        // Register the main class to use with the facade
        $this->app->bind('searchable-resource', SearchableResourceBuilder::class);
    }
}
