<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class ArrayableTest extends TestCase
{
    public function test_arrayable()
    {
        $this->assertIsArray(SearchableResource::make(User::query())
            ->paginate(4)
            ->toArray()
        );
    }
}
