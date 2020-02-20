<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class OrderableTest extends TestCase
{

    public function test_default_order_by_id_desc()
    {
        factory(MockUser::class)->create([
            'name' => 'A',
            'role' => 'admin',
        ]);
        factory(MockUser::class)->create([
            'name' => 'B',
            'role' => 'user',
        ]);
        factory(MockUser::class)->create([
            'name' => 'C',
            'role' => 'guest',
        ]);

        $this->json('get', route('orderable', []))
            ->assertOk()
            ->assertJson([
                'query' => [
                    'sort'     => 'desc',
                    'order_by' => 'id',
                ],
                'data'  => [
                    ['id' => 3, 'name' => 'C', 'role' => 'guest'],
                    ['id' => 2, 'name' => 'B', 'role' => 'user'],
                    ['id' => 1, 'name' => 'A', 'role' => 'admin'],
                ],
            ], true);
    }

    public function test_order_by_name_desc()
    {
        factory(MockUser::class)->create([
            'name' => 'A',
            'role' => 'admin',
        ]);
        factory(MockUser::class)->create([
            'name' => 'B',
            'role' => 'user',
        ]);
        factory(MockUser::class)->create([
            'name' => 'C',
            'role' => 'guest',
        ]);

        $this->json('get', route('orderable', [
            'order_by' => 'name',
            'sort'     => 'desc',
        ]))
            ->assertOk()
            ->assertJson([
                'query' => [
                    'sort'     => 'desc',
                    'order_by' => 'name',
                ],
                'data'  => [
                    ['id' => 3, 'name' => 'C', 'role' => 'guest'],
                    ['id' => 2, 'name' => 'B', 'role' => 'user'],
                    ['id' => 1, 'name' => 'A', 'role' => 'admin'],
                ],
            ], true);
    }

    public function test_order_by_role_desc()
    {
        factory(MockUser::class)->create([
            'name' => 'A',
            'role' => 'admin',
        ]);
        factory(MockUser::class)->create([
            'name' => 'B',
            'role' => 'user',
        ]);
        factory(MockUser::class)->create([
            'name' => 'C',
            'role' => 'guest',
        ]);
        $this->json('get', route('orderable', [
            'order_by' => 'role',
            'sort'     => 'desc',
        ]))
            ->assertOk()
            ->assertJson([
                'query' => [
                    'sort'     => 'desc',
                    'order_by' => 'role',
                ],
                'data'  => [
                    ['id' => 2, 'name' => 'B', 'role' => 'user'],
                    ['id' => 3, 'name' => 'C', 'role' => 'guest'],
                    ['id' => 1, 'name' => 'A', 'role' => 'admin'],
                ],
            ], true);
    }

    public function test_invalid_order()
    {
        $this->json('get', route('orderable', [
            'order_by' => 'invalid',
            'sort'     => 'invalid',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_by',
                'sort',
            ]);
    }
}
