<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\SearchableBuilder;
use BayAreaWebPro\SearchableResource\SearchableResourceServiceProvider;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class ProviderTest extends TestCase
{
    public function test_provider_is_registered()
    {
        $this->assertInstanceOf(
            SearchableResourceServiceProvider::class,
            $this->app->getProvider(SearchableResourceServiceProvider::class), 'Provider is registered with container.');
    }

    public function test_container_can_resolve_instance()
    {
        $this->assertInstanceOf(
            SearchableBuilder::class,
            $this->app->make('searchable-resource'), 'Container can make instance of service.');
    }

    public function test_facade_can_resolve_instance()
    {
        $this->assertInstanceOf(
            SearchableBuilder::class,
            \SearchableResource::getFacadeRoot(), 'Alias class can make instance of service.');

        $this->assertInstanceOf(
            SearchableBuilder::class,
            \BayAreaWebPro\SearchableResource\SearchableResource::getFacadeRoot(), 'Facade can make instance of service.');
    }

    public function test_service_can_be_resolved()
    {
        $instance = app('searchable-resource');
        $this->assertTrue($instance instanceof SearchableBuilder);
    }

    public function test_declares_provided()
    {
        $this->assertTrue(in_array('searchable-resource',
                collect(app()->getProviders(SearchableResourceServiceProvider::class))->first()->provides())
        );
    }
}
