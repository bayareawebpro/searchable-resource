<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\UserQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Resources\MockResource;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Validation\ValidationException;

class ResourceTest extends TestCase
{
    public function test_will_use_specified_resource()
    {
        factory(User::class)->create([
            'name' => 'Test',
        ]);
        $this->json('get', route('resource', []))
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Test',
                        'appended' => true,
                    ]
                ],
            ])
        ;
    }
}
