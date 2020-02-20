<?php

use BayAreaWebPro\SearchableResource\OptionsFormatter;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\SearchableBuilder;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\MockOptionsQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\MockRoleQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\MockUserQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Resources\MockResource;
use Illuminate\Support\Facades\Route;

/**
 * Orderable Test
 */
Route::get('orderable', fn() => (
SearchableResource::make(MockUser::query())
    ->orderable([
        'name',
        'role',
    ])
))->name('orderable');

/**
 * Appendable Test
 */
Route::get('appendable', fn() => (
SearchableResource::make(MockUser::query())
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
SearchableResource::make(MockUser::query())
    ->paginate(4)
))->name('paginated');

/**
 * NonPaginated Test
 */
Route::get('non-paginated', fn() => (
SearchableResource::make(MockUser::query())
))->name('non-paginated');

/**
 * Resource Test
 */
Route::get('resource', fn() => (
SearchableResource::make(MockUser::query())
    ->resource(MockResource::class)
))->name('resource');

/**
 * Query Test
 */
Route::get('queries', fn() => (
SearchableResource::make(MockUser::query())
    ->queries([
        MockUserQuery::class,
        MockRoleQuery::class,
    ])
))->name('queries');

/**
 * Labeled Test
 */
Route::get('labeled', fn() => (
SearchableResource::make(MockUser::query())
    ->query(MockOptionsQuery::make())
    ->useFormatter(new OptionsFormatter)
    ->paginate(4)
    ->labeled()
))->name('labeled');

/**
 * With Data Test
 */
Route::get('with', fn() => (
SearchableResource::make(MockUser::query())
    ->with([
        'with' => true,
    ])
))->name('with');
/**
 * With Data Test
 */

Route::get('when', fn() => (
SearchableResource::make(MockUser::query())
    ->when(request()->filled('class'), new class{
        public function __invoke(SearchableBuilder $builder){
            $builder->with([
                'class' => request()->get('class')
            ]);
        }
    })
    ->when(request()->filled('closure'), function(SearchableBuilder $builder){
        $builder->with([
            'closure' => request()->get('closure')
        ]);
    })
))->name('when');

/**
 * With Options
 */
Route::get('options', fn() => (
SearchableResource::make(MockUser::query())
    ->queries([
        MockOptionsQuery::class,
    ])
))->name('options');

/**
 * Validation Test
 */
Route::get('validation', fn() => (
SearchableResource::make(MockUser::query())
    ->queries([
        MockUserQuery::class,
        MockRoleQuery::class,
    ])
))->name('validation');

/**
 * Select Test
 */
Route::get('select', fn() => (
SearchableResource::make(MockUser::query())
    ->select([
        'id',
    ])
))->name('select');
