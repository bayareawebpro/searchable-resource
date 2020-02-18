<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class QueryTest extends TestCase
{
    public function test_default_query()
    {
        factory(User::class)->create([
            'name'  => 'Tester 1',
            'email' => 'tester1@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 2',
            'email' => 'tester2@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 3',
            'email' => 'tester3@test.com',
        ]);

        $this->json('get', route('queries', []))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['name' => 'Tester 3', 'email' => 'tester3@test.com'],
                    ['name' => 'Tester 2', 'email' => 'tester2@test.com'],
                    ['name' => 'Tester 1', 'email' => 'tester1@test.com'],
                ],
            ], true);
    }

    public function test_search_for_name()
    {
        factory(User::class)->create([
            'name'  => 'Tester 1',
            'email' => 'tester1@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 2',
            'email' => 'tester2@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 3',
            'email' => 'tester3@test.com',
        ]);

        $this->json('get', route('queries', ['search' => 'Tester 3']))
            ->assertOk()
            ->assertJson([
                'data'  => [
                    ['name' => 'Tester 3', 'email' => 'tester3@test.com'],
                ],
                'query' => ['search' => 'Tester 3'],
            ], true);

        $this->json('get', route('queries', ['search' => 'tester2@test.com']))
            ->assertOk()
            ->assertJson([
                'data'  => [
                    ['name' => 'Tester 2', 'email' => 'tester2@test.com'],
                ],
                'query' => ['search' => 'tester2@test.com'],
            ], true);
    }

    public function test_search_for_email()
    {
        factory(User::class)->create([
            'name'  => 'Tester 1',
            'email' => 'tester1@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 2',
            'email' => 'tester2@test.com',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 3',
            'email' => 'tester3@test.com',
        ]);

        $this->json('get', route('queries', ['search' => 'tester2@test.com']))
            ->assertOk()
            ->assertJson([
                'data'  => [
                    ['name' => 'Tester 2', 'email' => 'tester2@test.com'],
                ],
                'query' => ['search' => 'tester2@test.com'],
            ], true);
    }

    public function test_filter_by_role()
    {
        factory(User::class)->create([
            'name'  => 'Tester 1',
            'email' => 'tester1@test.com',
            'role' => 'admin',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 2',
            'email' => 'tester2@test.com',
            'role' => 'editor',
        ]);
        factory(User::class)->create([
            'name'  => 'Tester 3',
            'email' => 'tester3@test.com',
            'role' => 'guest',
        ]);

        $this->json('get', route('queries', ['role' => 'guest']))
            ->assertOk()
            ->assertJson([
                'data'  => [
                    ['name' => 'Tester 3', 'email' => 'tester3@test.com'],
                ],
                'query' => ['role' => 'guest'],
            ], true)

            ->assertJsonMissing([
                'query' => ['search' => 'guest'],
            ], true);
    }
}
