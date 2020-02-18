<?php

use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\RoleQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\UserQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Resources\MockResource;
use Illuminate\Support\Facades\Route;

/**
 * Orderable Test
 */
Route::get('orderable', fn() => (
    SearchableResource::make(User::query())
        ->orderable([
            'name',
            'role',
        ])
))->name('orderable');

/**
 * Appendable Test
 */
Route::get('appendable', fn() => (
SearchableResource::make(User::query())
    ->appendable([
        'test',
    ])
    ->withFields([
        'name'
    ])
))->name('appendable');


/**
 * Paginated Test
 */
Route::get('paginated', fn() => (
SearchableResource::make(User::query())
    ->paginate(4)
))->name('paginated');

/**
 * NonPaginated Test
 */
Route::get('non-paginated', fn() => (
SearchableResource::make(User::query())
))->name('non-paginated');

/**
 * Resource Test
 */
Route::get('resource', fn() => (
SearchableResource::make(User::query())
    ->resource(MockResource::class)
))->name('resource');

/**
 * Query Test
 */
Route::get('queries', fn() => (
SearchableResource::make(User::query())
    ->queries([
        UserQuery::class,
        RoleQuery::class,
    ])
))->name('queries');

/**
 * Labeled Test
 */
Route::get('labeled', fn() => (
SearchableResource::make(User::query())
    ->paginate(4)
    ->labeled()
))->name('labeled');

/**
 * Validation Test
 */
Route::get('validation', fn() => (
SearchableResource::make(User::query())
    ->paginate(4)
    ->queries([
        UserQuery::class,
        RoleQuery::class,
    ])
    ->labeled()
))->name('validation');
