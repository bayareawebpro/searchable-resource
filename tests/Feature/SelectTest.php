<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class SelectTest extends TestCase
{
    public function test_will_append_options_from_rules()
    {
        factory(MockUser::class)->create([
            'name' => 'Test',
        ]);
        $this->json('get', route('select', []))
            ->assertJson([
                'data' => [
                    [
                        'id' => 1,
                    ]
                ],
            ], true)
            ->assertJsonMissing([
                'data' => [
                    [
                        'name' => 'Test',
                    ]
                ],
            ], true)
        ;
    }
}
