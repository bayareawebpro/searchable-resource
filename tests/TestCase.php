<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\SearchableResourceServiceProvider;
use Illuminate\Foundation\Application;

class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Load package service provider
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [SearchableResourceServiceProvider::class];
    }

    /**
     * Load package alias
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'SearchableResource' => SearchableResource::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__.'/Fixtures/Factories');
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/Migrations');
        require __DIR__.'/Fixtures/routes.php';
    }
}
