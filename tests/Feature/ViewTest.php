<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ViewTest extends TestCase
{
    public function test_can_use_pagination_sorting()
    {
        $resource = SearchableResource::make(MockUser::query())
            ->orderBy('name')
            ->orderable([
                'name',
                'email'
            ])
            ->sort('desc')
            ->paginate(4)
            ->execute()
        ;

        $this->assertInstanceOf(LengthAwarePaginator::class, $resource->getItems());
        $this->assertInstanceOf(Collection::class, $resource->getOptions());
        $this->assertSame('name', $resource->getOrderBy());
        $this->assertSame('desc', $resource->getSort());
        $this->assertSame(4,$resource->getPerPage());
        $this->assertSame(1,$resource->getPage());
        $this->assertNull($resource->getSearch());


        request()->merge([
            'search' => 'Test'
        ]);

        $resource->execute();

        $this->assertEquals('Test', $resource->getSearch());
    }

    public function test_can_use_search()
    {

        request()->merge([
            'search' => 'Test'
        ]);
        $resource = SearchableResource::make(MockUser::query())
            ->orderBy('name')
            ->orderable([
                'name',
                'email'
            ])
            ->sort('desc')
            ->paginate(4)
            ->execute()
        ;

        $resource->execute();

        $this->assertEquals('Test', $resource->getSearch());
    }
}
