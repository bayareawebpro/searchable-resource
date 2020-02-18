<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

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
