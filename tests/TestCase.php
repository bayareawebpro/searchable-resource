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
     * Define environment setup.
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
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
