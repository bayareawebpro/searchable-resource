<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class PaginationTest extends TestCase
{
    public function test_invalid_pagination()
    {
        $this->json('get', route('paginated', [
            'per_page' => 110,
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'per_page',
        ]);
    }

    public function test_valid_pagination()
    {
        factory(MockUser::class, 6)->create();

        $this->json('get', route('paginated', [
            'per_page' => 4,
        ]))
        ->assertOk()
        ->assertJson([
            'pagination' =>[
                'from' => 1,
                'to' => 4,
                'isFirstPage' => true,
                'isLastPage' => false,
            ],
            'query' =>[
                'page' => 1,
                'per_page' => 4,
            ],
        ], true);

        $this->json('get', route('paginated', [
            'per_page' => 4,
            'page' => 2,
        ]))
        ->assertOk()
        ->assertJson([
            'pagination' =>[
                'from' => 5,
                'to' => 6,
                'isFirstPage' => false,
                'isLastPage' => true,
            ],
            'query' =>[
                'page' => 2,
                'per_page' => 4,
            ],
            'options' =>[
                'per_page' => config('searchable-resource.per_page_options', []),
            ],
        ], true);
    }

    public function test_non_paginated()
    {
        factory(MockUser::class, 6)->create();

        $this->json('get', route('non-paginated', [
            'per_page' => 4,
        ]))
        ->assertJson([
            'options' =>[
                'sort' => [],
                'order_by' => [],
            ],
        ], true)
        ->assertJsonMissing([
            'pagination' =>[
                'from' => 5,
                'to' => 6,
                'isFirstPage' => false,
                'isLastPage' => true,
            ],
            'query' =>[
                'page' => 2,
                'per_page' => 4,
            ],
            'options' =>[
                'per_page' => config('searchable-resource.per_page_options', []),
            ],
        ], true);
    }
}
