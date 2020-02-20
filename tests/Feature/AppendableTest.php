<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class AppendableTest extends TestCase
{
    public function test_will_append_appendable()
    {
        factory(MockUser::class)->create([
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
        factory(MockUser::class)->create([
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
