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
    protected string $field = 'search';
    protected string $attribute = 'name';

    public function __invoke(Builder $builder): void
    {
        $builder->where($this->getAttribute(), "like", "%{$this->getValue()}%");
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
    protected string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->getAttribute(), $this->getValue());
    }

    public function getApplies(): bool
    {
    	return parent::getApplies() && in_array($this->getValue(), ['user', 'admin']);
    }
}
```

---

### Validation

Queries can specify their own validation rules by implementing the `ValidatableQuery` 
contract.  The following rules are automatically merged into the collected rules 
from your queries.  

```php
[
    'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
    'page'     => ['sometimes', 'numeric', 'min:1', 'max:'.PHP_INT_MAX],
    'sort'     => ['sometimes', 'string', Rule::in($this->getSortOptions()->all())],
    'order_by' => ['sometimes', 'string', Rule::in($this->getOrderableOptions()->all())],
    'per_page' => ['sometimes', 'numeric', Rule::in($this->getPerPageOptions()->all())],
];
```

### ValidatableQuery Contract

Queries that implement the `ValidatableQuery` Contract will have their returned rules 
merged into the validator.  Otherwise the rules will be ignored.

```php
<?php declare(strict_types=1);

namespace App\Queries;
 
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;
 
class ConditionalRoleQuery extends AbstractQuery implements ValidatableQuery
{

    protected string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->getAttribute(), $this->getValue());
    }

    public function getRules(): array
    {
        return [
            $this->getField() => 'required|string|in:admin,editor,guest',
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

    protected string $field = 'role';
    protected string $attribute = 'role';
 
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->getAttribute(), $this->getValue());
    }

    public function getOptions(): array
    {
        return [
            $this->getField() => [
                'admin', 'editor'
            ],
        ];
    }
}
```

### Options Formatting

Request options are appended to the output for convenience / UI State.  Options can 
be auto-formatted with labels for usage with forms and filters by calling 
the `labeled()` method on the builder.  The labeled method accept a boolean 
value which can be used to enable when the request has a session.

```
"options": {
    "role": [
        {
            "value": "admin" 
            "label": "Admin"
        },
        {
            "value": "editor" 
            "label": "Editor"
        },
    ]
}
```

### FormatsOptions Contract

You can override the default formatter by specifying a formatter instance.

```php
SearchableResource::make(User::query())->useFormatter(new OptionsFormatter);
```

```php
<?php declare(strict_types=1);

namespace App\Http\Resources;

use BayAreaWebPro\SearchableResource\OptionsFormatter as Formatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    /**
     * @param Collection $options
     * @param string $append
     * @return Collection
     */
    protected function append(Collection $options, string $append): Collection
    {
        return $options->map(fn($value, $key)=>[
            'label' => "$value $append",
            'value' => $value,
        ]);
    }

    /**
     * @param Collection $options
     * @param string $label
     * @return Collection
     */
    protected function nullable(Collection $options, string $label = 'All'): Collection
    {
        return $options->prepend([
            'label' => $label,
            'value' => null,
        ]);
    }

    /**
     * @param Collection $options
     * @return Collection
     */
    protected function titleCase(Collection $options): Collection
    {
        return $options->map(fn($value, $key) => [
            'label' => Str::title(str_replace("_", " ", "$value")),
            'value' => $value,
        ]);
    }

    /**
     * @param Collection $options
     * @return Collection
     */
    protected function literal(Collection $options): Collection
    {
        return $options->map(fn($value, $key) => [
            'label' => $value,
            'value' => $value,
        ]);
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

Second by instantiating each query using the `make` method.  This can be useful when you need 
more methods and logic to determine usage. 

```php

use App\Queries\RoleQuery;

SearchableResource::make(User::query())
	->query(RoleQuery::make());
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
        'items' =>$resource->items(),
        'options' =>$resource->options(),
    ]);
}
```

```html
<x-select name="sort"       value="{{ request('sort') }}"       :options="$options->get('sort')"/>
<x-select name="order_by"   value="{{ request('order_by') }}"   :options="$options->get('order_by')"/>
<x-select name="per_page"   value="{{ request('per_page') }}"   :options="$options->get('per_page')"/>
```

### Vue Components Example

> Component Usage: 

```vue
<script>
    export default {
        name: "UserResource",
        data: ()=>({ resource: {} }),
        watch: {
            '$route': 'syncResource'
        },
        methods:{
            async syncResource({params,query}){
                try{
                    const {data} = await axios.get('/users', {params: {
                        ...params, 
                        ...query
                    }})
                    this.resource = data               
                }catch (e) {
                    console.error(e)
                }
            }
        }
    }
</script>
<template>
    <v-resource
        v-model="resource"
        :searchable="true">
        <template v-slot:title>
            Users
        </template>
        <template v-slot:actions>
            <router-link
                dusk="action-create"
                class="btn btn-blue"
                :to="{name: 'users.create'}">
                Add
            </router-link>
        </template>
        <template v-slot:filters="{filter, query,pagination,options}">
            <div class="grid-item ml-auto">
                <button
                    @click="filter({page: 1, role: null})"
                    :disabled="!query.role"
                    class="btn btn-xs"
                    dusk="filter-role-all">
                    All
                </button>
                <button
                    @click="filter({page: 1, role: 'admin'})"
                    :disabled="query.role ==='admin'"
                    class="btn btn-xs btn-green"
                    dusk="filter-role-admin">
                    Admins
                </button>
                <button
                    @click="filter({page: 1, role: 'editor'})"
                    :disabled="query.role ==='editor'"
                    class="btn btn-xs btn-yellow"
                    dusk="filter-role-admin">
                    Editor
                </button>
                <button
                    @click="filter({page: 1, role: 'guest'})"
                    :disabled="query.role ==='guest'"
                    class="btn btn-xs btn-red"
                    dusk="filter-role-guest">
                    Editor
                </button>
            </div>
        </template>
        <template v-slot:entries="{entries}">
            <div v-for="entry in entries" dusk="entry" class="card mb-2" >
                <div class="card-header p-2">
                    <div class="grid">
                        <div class="grid-item text-xs">
                            <router-link
                                dusk="entry-show"
                                class="block text-sm"
                                :to="{name: 'users.show', params: {id: entry.id}}">
                                {{ entry.name }}
                            </router-link>
                            {{ entry.email }}
                        </div>
                        <div class="grid-item text-xs ml-auto">
                            <div class="badge float-right" :class="{
                                'badge-yellow': entry.role === 'editor',
                                'badge-green': entry.role === 'admin',
                                'badge-red': entry.role === 'guest',
                            }">
                                {{ entry.role }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-actions p-2">
                    <div class="grid">
                        <div class="grid-item text-xs">
                            Created: {{ entry.created_at }} |
                            Updated: {{ entry.updated_at }}
                        </div>
                        <div class="grid-item ml-auto">
                            <button
                                dusk="entry-destroy"
                                @click="destroying = entry" class="btn-red btn-xs ml-auto">
                                <i class="fa fa-trash"/> Destroy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </v-resource>
</template>
```

#### Resource

> Main Component for the Resource.

```vue
<script>
    export default {
        name: "Resource",
        props: {
            value: {required: true},
            searchable: {required: true},
        },
        methods:{
            filter(newQuery){
                this.$router.push({query: {...this.$route.query, ...newQuery}},
                    () => this.$emit('updated'),
                    () => this.$emit('failed'))
            }
        }
    }
</script>
<template>
    <div class="text-white" v-if="value">
        <div class="flex mb-2">
            <div class="hidden sm:block flex-shrink self-center mr-3 text-blue-200">
                <slot name="title">Resource</slot>
            </div>
            <div class="flex-grow mr-3">
                <!--SEARCHABLE-->
                <v-searchable
                    v-if="searchable"
                    v-model="value.query.search"
                />
            </div>
            <div class="flex-shrink self-center">
                <slot name="actions"/>
            </div>
        </div>
        <div class="grid my-4">
            <div class="grid-item pr-0">
                <!--SORT / ORDERABLE-->
                <select 
                    v-model="value.query.sort" 
                    @change="filter({sort: value.query.sort, page: 1})">
                    <option 
                        v-for="option in value.options.sort" 
                        :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <div class="grid-item">
                <select 
                    v-model="value.query.per_page" 
                    @change="filter({sort: value.query.per_page, page: 1})">
                    <option 
                        v-for="option in value.options.per_page" 
                        :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <div class="grid-item pl-0">
                <select 
                    v-model="value.query.order_by" 
                    @change="filter({sort: value.query.order_by, page: 1})">
                    <option 
                        v-for="option in value.options.order_by" 
                        :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            <slot
                name="filters"
                :pagination="value.pagination"
                :options="value.options"
                :query="value.query"
                :filter="filter"
            />
        </div>
        <div v-if="value.data.length">
            <slot name="entries" :entries="value.data"/>
            <v-pagination v-model="value.pagination"/>
        </div>
        <div v-else class="alert info">
            No Results.
        </div>
    </div>
</template>
```


#### Pagination
> Child Component for Resource Pagination.

```vue
<script>
    export default {
        name: "Pagination",
        props: {
            value: {required: true},
            listen: {default: () => false},
        },
        computed: {
            page() {
                return Number(this.$route.query.page || this.value.current_page || 1)
            }
        },
        methods: {
            prevPage() {
                if (this.value.isFirstPage) return;
                this.$router.push({
                    query: {...this.$route.query, page: this.page - 1}
                }, () => this.$emit('prevPage'), () => this.$emit('failed'))
            },
            nextPage() {
                if (this.value.isLastPage) return;
                this.$router.push({
                    query: {...this.$route.query, page: this.page + 1},
                }, () => this.$emit('nextPage'), () => this.$emit('failed'))
            }
        }
    }
</script>
<template>
    <div class="grid mt-4">
        <div class="grid-item pr-1">
            <button @click="prevPage" :disabled="value.isFirstPage">
                Prev
            </button>
        </div>
        <div class="grid-item pl-1">
            <button @click="nextPage" :disabled="value.isLastPage">
                Next
            </button>
        </div>
        <div v-if="value.current_page && value.last_page">
            Page <strong>{{ value.current_page }}</strong> of <strong>{{ value.last_page }}</strong>
        </div>
        <div v-if="value.total">
            <strong>{{ value.total }}</strong> Total Entities
        </div>
    </div>
</template>
```

#### Search Input

> Child Component for the Resource Search Box.

```vue
<script>
    export default {
        name: "Searchable",
        props: {
            value: {required: true},
        },
        computed: {
            keywords: {
                set(val) {
                    this.$emit('input', val)
                },
                get() {
                    return this.value
                }
            }
        },
        methods: {
            search() {
                try {
                    this.$router.push({query: {...this.$route.query, search: this.keywords}})
                } catch (e) {
                    console.error(e)
                }
            }
        },
    }
</script>
<template>
    <div class="element relative">
        <label
            for="search"
            class="hidden"
            aria-hidden="true">
            Search
        </label>
        <input
           dusk="search"
           id="search"
           type="search"
           name="search"
           class="input search"
           v-model="keywords"
           placeholder="Search..."
           @keydown.enter="search">
    </div>
</template>
```

### Testing

``` bash
composer test
composer lint
```
