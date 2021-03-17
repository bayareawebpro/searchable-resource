# Laravel Searchable Resource Builder

![CI](https://github.com/bayareawebpro/searchable-resource/workflows/ci/badge.svg)
![Coverage](https://codecov.io/gh/bayareawebpro/searchable-resource/branch/master/graph/badge.svg)
![Downloads](https://img.shields.io/packagist/dt/bayareawebpro/searchable-resource.svg)
![Version](https://img.shields.io/github/v/release/bayareawebpro/searchable-resource.svg)
![MIT](https://img.shields.io/badge/License-MIT-success.svg)

Searchable Resource Builder is an abstraction for building 
searchable resource responses in Laravel applications. Extract 
query logic into reusable chunks while using a fluent builder 
interface for dealing with searchable / filterable / sortable 
requests and JSON / API Resources.

```bash
composer require bayareawebpro/searchable-resource
```

### Basic Usage

SearchableResources implement the `Responsable` interface which allows them to be 
returned from controllers easily. 

The ```make``` method accepts instances of Eloquent Builder.  

```php
use App\User;
use App\Post;

use BayAreaWebPro\SearchableResource\SearchableResource;

SearchableResource::make(User::query());
SearchableResource::make(Post::forUser(request()->user())); 
```
	
### Ordering and Sorting

You can specify as many orderable columns as you wish.

```php
SearchableResource::make(User::query())
	->orderable(['name', 'email'])
	->orderBy('name')
	->sort('desc')
	->paginate(16);
```

The default settings:

* order_by ID
* sort DESC

---

### Full Example

```php
use App\User;
use App\Queries\UserSearch;
use App\Queries\RoleFilter;
use App\Http\Resources\UserResource;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\SearchableBuilder;

SearchableResource::make(User::query())
    ->resource(UserResource::class)
    ->queries([
        UserSearch::class,
        RoleFilter::class
    ])
    ->orderable([
        'id', 'name', 'email', 'role',
        'created_at', 'updated_at',
    ])
    ->appendable([
        'created_for_humans',
        'updated_for_humans',
    ])
    ->select([
        'id',
        'name', 'email', 'role',
        'created_at', 'updated_at',
    ])
    ->fields([
       'my_filter_key'
    ])
    ->rules([
       'my_filter_key' => 'required'
    ])
    ->with([
        'my_key' => true
    ])
    ->when(true, fn(SearchableBuilder $builder)=>$builder
        ->with([
            'my_key' => false
        ])
    )
    ->orderBy('updated_at')
    ->sort('desc')
    ->paginate(16)
    ->labeled();
```
---

### Blade / View Example

Execute the query and return a view with the items and options.

```php
public function index()
{
    $resource = SearchableResource::make(User::query())
        ->query(Users::make())
        ->orderable(['name', 'email'])
        ->orderBy('name')
        ->sort('desc')
        ->paginate(5)
        ->execute()
    ;
    return view('users.index', [
        'items' =>$resource->getItems(),
        'search' =>$resource->getSearch(),
        'order_by' =>$resource->getOrderBy(),
        'per_page' =>$resource->getPerPage(),
        'options' =>$resource->getOptions(),
        'sort' =>$resource->getSort(),
    ]);
}
```

```html
<x-admin::form method="GET">
    <x-admin::search
        name="search"
        value="{{ $search ?? null }}"
    />
    <x-admin::select
        name="order_by"
        value="{{ $order_by ?? null }}"
        :options="$options->get('order_by')"
    />
    <x-admin::select
        name="sort"
        value="{{ $sort ?? null }}"
        :options="$options->get('sort')"
    />
    <x-admin::select
        name="per_page"
        value="{{ $per_page ?? null }}"
        :options="$options->get('per_page')"
    />
    <x-admin::submit
        label="{{ __('resources.filter') }}"
    />
</x-admin::form>
```

---

### JSON Resources

SearchableResources are generic JsonResources by default.  You can easily specify 
which resource class should be used to map your models when building the response.

> Must extend `JsonResource`.

```php
SearchableResource::make(User::query())->resource(UserResource::class);
```

---

### Invokable Queries

Queries are expressed as invokable classes that extend the `AbstractQuery` class 
which contains logic per request field.  Queries can apply to multiple attributes/columns
but should pertain to a single input.  

`php artisan make:searchable NameQuery`

The following is an example of a generic name query:  

```php
<?php declare(strict_types=1);
 
namespace App\Queries;
 
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;
 
class NameLikeQuery extends AbstractQuery
{
    public string $field = 'search';
    protected string $attribute = 'name';
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, "like", "%{$this->getValue($this->field)}%");
    }
}
```

### ConditionalQuery Contract

Queries that implement the `ConditionalQuery` Contract will only be applied when 
their `applies` method returns `true`. 
 
By default an query that extends `AbstractQuery` class using the `ConditionalQuery` contract 
already implements this method for you by calling the `filled` method on the request.  
Override the parent method to customize.

```php
<?php declare(strict_types=1);

namespace App\Queries;
 
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
 
class ConditionalRoleQuery extends AbstractQuery implements ConditionalQuery
{
    public string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, $this->getValue($this->field));
    }

    public function getApplies(): bool
    {
    	return parent::getApplies(); // Customize with $this->request
    }
}
```

---

### Validation

Queries can specify their own validation rules by implementing the `ValidatableQuery` 
contract to be merged the rules for the searchable collection.

### ValidatableQuery Contract

Queries that implement the `ValidatableQuery` Contract will have their returned rules 
merged into the validator, otherwise the rules will be ignored.

```php
<?php declare(strict_types=1);

namespace App\Queries;
 
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;
 
class ConditionalRoleQuery extends AbstractQuery implements ValidatableQuery
{

    public string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, $this->getValue($this->field));
    }

    public function getRules(): array
    {
        return [
           $this->field => 'required|string|in:admin,editor,guest',
        ];
    }
}
```

### ProvidesOptions Contract

Queries can provide options that will be appended to the request options 
data by implementing the `ProvidesOptions` contract.  This method should return 
a flat array of values that are injected into the response query options data.


For example let's say we have a select field 
query that implements it's own builder interface:

```php
use App\Queries\SelectQuery;

SearchableResource::make(User::query())
    ->query(
        SelectQuery::make()
            ->field('user_role') // Request Field
            ->attribute('role')  // Table Column
            ->default('user') 	 // Default Value
            ->values([
                'user', 'editor','admin' //Rule (in)
            ])
    );
```

```php
<?php declare(strict_types=1);

namespace App\Queries;
 
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ProvidesOptions;
 
class ProvidesOptionsQuery extends AbstractQuery implements ProvidesOptions
{

    public string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, $this->getValue($this->field));
    }

    public function getOptions(): array
    {
        return [
            $this->field => [
                'admin', 'editor'
            ],
        ];
    }
}
```

### Options Formatting

Options can be formatted with labels for usage with forms and filters by calling 
the `labeled()` method on the builder.  The labeled method accept a boolean 
value which can be used to enable when the request has a session.

> You can return preformatted options (label / value array) from queries 
> or use the formatter to generate labeled options.

### API Options Schema

Options for API requests are typically not-formatted for speed.

```
public function getOptions(): array
{
    return [
        'role' => [
            'admin',
            'customer'
        ]
    ];
}
```

### Blade Options Schema

Options for Blade requests can be formatted for usability.

```
public function getOptions(): array
{
    return [
        $this->field => [
            [
                'label' => 'Admin',
                'value' => 'admin'
            ],
            [
                'label' => 'Customer',
                'value' => 'customer'
            ]
        ]
    ];
}
```

### FormatsOptions Contract

You can override the default formatter by specifying a formatter instance.

```php
SearchableResource::make(User::query())->useFormatter(new OptionsFormatter);
```

```php
<?php declare(strict_types=1);

namespace App\Http\Resources\Formatters;

use Illuminate\Support\Collection;
use BayAreaWebPro\SearchableResource\OptionsFormatter as Formatter;

class OptionsFormatter extends Formatter {

    /**
     * @param string $key
     * @param Collection $options
     * @return Collection
     */
    public function __invoke(string $key, Collection $options): Collection
    {
        if($key === 'abilities'){
            return $this->nullable($this->literal($options));
        }
        if($key === 'role'){
            return $this->nullable($this->titleCase($options));
        }
        return $this->baseOptions($key, $options);
    }
}
```

### Setting Up Default Options

You can setup a resolving callback in a service provider to pre-bind options to every instance.

```php

use BayAreaWebPro\SearchableResource\OptionsFormatter;
use BayAreaWebPro\SearchableResource\SearchableBuilder;

$this->app->resolving(
    SearchableBuilder::class,
    function (SearchableBuilder $builder){
    return $builder
        ->useFormatter(new OptionsFormatter)
        ->labeled(request()->hasSession())
        ->orderBy('created_at')
        ->paginate(8)
        ->sort('desc')
    ;
});
```
---

### Adding Queries:

Queries can be added two ways.  First by referencing the class string for easy bulk usage.

```php
use App\Queries\RoleQuery;

SearchableResource::make(User::query())
	->queries([
		RoleQuery::class
	]);
```

Second by instantiating each query using the make method.  This can be useful when you need 
more methods and logic to determine usage. 

```php
$searchable = SearchableResource::make(User::query());
```

---

### Appendable Data

Attributes and fields can be appended to the response by using the following methods: 

**For model attributes:** 

```php
SearchableResource::make(User::query())
    ->appendable([
        'created_for_humans',
        'updated_for_humans',
        'bytes_for_humans',
    ]);
```

**For additional data (appended to the response):** 

```php
SearchableResource::make(User::query())
    ->with([
        'my_key' => []
    ]);
```

```json
{
    "my_key": [],
    "data": []
}
```

**For request fields (appended to the query in response):** 

```php
SearchableResource::make(User::query())
    ->fields([
        'my_filter_state'
    ]);
```

```json
{
    "query": {
        "my_filter_state": true
    }
}
```

---

### When Condition Callback

You can use a callback or invokable class for more control with less method chaining.

```php

class SessionEnabledQuery{
    public function __invoke(SearchableBuilder $builder): void 
    {
        $builder->labeled();
    }
}

SearchableResource::make(User::query())
    ->when(request()->hasSession(), new SessionEnabledQuery)
    ->when(request()->hasSession(), function(SearchableBuilder $builder){
        $builder->labeled();
    })
;
```
---

### Tap Callback

Useful for configuring the builder via an invokable class.

```php

use BayAreaWebPro\SearchableResource\SearchableBuilder;
use BayAreaWebPro\SearchableResource\Contracts\InvokableBuilder;
class UserSearchable implements InvokableBuilder{
    public function __invoke(SearchableBuilder $builder): void 
    {
        $builder->queries([
            RoleQuery::class
        ]);
    }
}

SearchableResource::make(User::query())->tap(new UserSearchable);
```

---

### Response Output

The relevant query parameters and request options are appended to the output for 
convenience.  Two additional properties have been added to the pagination parameters 
to remove the need for conditionals on the client / user side `isFirstPage` and `isLastPage` 
making pagination buttons easy to disable via props (Vue | React).

> Note: If the `pagination` method is not used, all pagination related properties 
> will be filtered from the output data.

```
"data": [
    //
],
"pagination": {
    "isFirstPage": true,
    "isLastPage": true,
    ...default pagination props...
},
"query": {
    "page": 1,
    "sort": "desc",
    "order_by": "id",	
    "search": "term",
    "per_page": 4,	
},
"options": {
    "orderable": [
        "id", 
        "name"
    ],
    "sort": [
        "asc"
        "desc"
    ]
}
```

---

### Testing

``` bash
composer test
composer lint
```
