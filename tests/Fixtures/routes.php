<?php

use BayAreaWebPro\SearchableResource\OptionsFormatter;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\SearchableResourceBuilder;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\OptionsQuery;
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
    ->fields([
        'name',
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
    ->query(OptionsQuery::make())
    ->useFormatter(new OptionsFormatter)
    ->paginate(4)
    ->labeled()
))->name('labeled');

/**
 * With Data Test
 */
Route::get('with', fn() => (
SearchableResource::make(User::query())
    ->with([
        'with' => true,
    ])
))->name('with');
/**
 * With Data Test
 */
Route::get('when', fn() => (
SearchableResource::make(User::query())
    ->when(request()->filled('when'), fn(SearchableResourceBuilder $builder)=>($builder->with([
        'with' => request()->get('when')
    ])))
))->name('when');

/**
 * With Options
 */
Route::get('options', fn() => (
SearchableResource::make(User::query())
    ->queries([
        OptionsQuery::class,
    ])
))->name('options');

/**
 * Validation Test
 */
Route::get('validation', fn() => (
SearchableResource::make(User::query())
    ->queries([
        UserQuery::class,
        RoleQuery::class,
    ])
))->name('validation');

/**
 * Select Test
 */
Route::get('select', fn() => (
SearchableResource::make(User::query())
    ->select([
        'id',
    ])
))->name('select');
