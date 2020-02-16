<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\UserQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Resources\MockResource;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Validation\ValidationException;

class AppendableTest extends TestCase
{
    public function test_will_append_appendable()
    {
        factory(User::class)->create([
            'name' => 'Test',
        ]);
        $this->json('get', route('appendable', []))->assertJson([
            'data' => [
                [
                    'name' => 'Test',
                    'test' => true,
                ],
            ],
        ], true);
    }

    public function test_will_append_fields_from_query()
    {
        factory(User::class)->create([
            'name' => 'Test',
        ]);

        $this->json('get', route('appendable', []))
            ->assertJsonMissing([
                'query' => ['name' => 'test'],
            ], true);

        $this->json('get', route('appendable', ['name' => 'test']))
            ->assertJson([
                'query' => ['name' => 'test'],
            ], true);
    }
}
