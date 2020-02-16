<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\SearchableResourceBuilder;
use BayAreaWebPro\SearchableResource\SearchableResourceServiceProvider;

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
            SearchableResourceBuilder::class,
            $this->app->make('searchable-resource'), 'Container can make instance of service.');
    }

    public function test_facade_can_resolve_instance()
    {
        $this->assertInstanceOf(
            SearchableResourceBuilder::class,
            \SearchableResource::getFacadeRoot(), 'Facade can make instance of service.');
    }

    public function test_service_can_be_resolved()
    {
        $instance = app('searchable-resource');
        $this->assertTrue($instance instanceof SearchableResourceBuilder);
    }
}
