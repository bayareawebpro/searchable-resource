# Laravel Searchable Resource Builder

![CI](https://github.com/bayareawebpro/laravel-simple-csv/workflows/ci/badge.svg)
![MIT](https://img.shields.io/badge/License-MIT-success.svg)
![Downloads](https://img.shields.io/packagist/dt/bayareawebpro/searchable-resource.svg)
![Version](https://img.shields.io/github/v/release/bayareawebpro/searchable-resource.svg)

Searchable Resource Builder is an abstraction for building 
searchable resource responses in Laravel applications.

Extract query logic into reusable chunks while using a fluent builder interface for dealing with searchable / filterable / sortable requests and JSON / API Resources.

## Installation
You can install the package via composer:

```bash
composer require bayareawebpro/searchable-resource
```

## Usage

* Sorting ```/my-route?sort=asc```
* Filtering ```/my-route?my_field=true```
* Ordering```/my-route?order_by=name```
* Searching ```/my-route?search=Taylor```
* Pagination ```/my-route?page=1&per_page=8```


The ```make``` method accepts instances of Eloquent Builder.  SearchableResources implement the `Responsable` interface which allows them to be returned from controllers easily. 

```php
use App\User;
use BayAreaWebPro\SearchableResource\SearchableResource;

return SearchableResource::make(User::query())->paginate(16);
```

#### Validation

Validation is compiled from your queries and options.

#### Ordering and Sorting

The default settings:

* OrderBy ID
* DESC

These options are automatically validated.  You can specify as many columns as you wish.  Queries can specify their own validation rules.

```php
SearchableResource::make(User::query())
	->orderBy('name')
	->sort('desc')
	->orderable([
		'name'
	]);
```

## Invokable Queries

Queries are expressed as invokable classes which contain logic per request field.  Queries can apply to multiple attributes but should pertain to a single input.  Rules can be contained within the query class itself.   The instance of the request will be passed into the rule when it's instantiated. 


```php
 <?php declare(strict_types=1);
 
 namespace App\Queries;
 
 use Illuminate\Http\Request;
 use Illuminate\Database\Eloquent\Builder;
 use BayAreaWebPro\SearchableResource\AbstractQuery;
 
 class RoleQuery extends AbstractQuery
 {
     protected string $field = 'role';
     protected string $attribute = 'role';
 
     public function __invoke(Builder $builder): void
     {
         $builder->where($this->attribute, $this->getValue());
     }
 
     public function rules(?Request $request = null): array
     {
         return [
             [$this->field => 'sometimes|string|max:255'],
         ];
     }
 }
```

Queries can be conditionally applied by implmenting the `ConditionalQuery` contract.

```php
<?php declare(strict_types=1);

namespace App\Queries;
 
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
 
class ConditionalRoleQuery extends RoleQuery implements ConditionalQuery
{
    public function applies(Request $request): bool
    {
    	return $this->filled($this->field);
    }
}
```

### Adding Queries:

Queries can be added two ways.  First by referencing the class string for easy bulk usage.

```php
use App\Queries\RoleQuery;

SearchableResource::make(User::query())
	->queries([
		RoleQuery::class
	]);
```

Second by instantiating each query using the make method.  This can be useful when you need more methods and logic to determine usage.

For example let's say we have a select field query that implments it's own builder interface:

```php
use App\Queries\SelectQuery;

$searchable = SearchableResource::make(User::query());

$searchable->query(
	SelectQuery::make()
		->field('user_role') // Request Field
		->attribute('role')  // Table Column
		->default('user') 	 // Default Value
		->options([
			'user', 'editor','admin' //Rule (in)
		])
);


return $searchable;

```

### Appendable Data

Attributes and fields can be appended to the response by using the following methods: 


For model attributes: 

```php
SearchableResource::make(User::query())
    ->appendable([
        'created_for_humans',
        'updated_for_humans',
        'bytes_for_humans',
    ]);
```

For request fields (appended to the query in response): 

```php
SearchableResource::make(User::query())
	->withFields([
		'name'
	]);
```

### API / JSON Resources

SearchableResources are generic JsonResources by default.  You can easily specify which resource class should be used to map your models when building the response.

> Must extend `JsonResource`.

```php
SearchableResource::make(User::query())
	->resource(UserResource::class)
	->paginate(8)
```

### Response Output

The relevant query parameters and request options are appended to the output for convenience.  Two additional properties have been added to the pagination parameters to remove the need for conditionals on the client / user side. `isFirstPage` and `isLastPage` making pagination buttons easy to disable via props (Vue | React).

> Note: If the `pagination` method is not used, all pagination related properties will be filtered from the output data.

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

### Options: Sessions / AirLock

Request options are appended to the output for convenience.  Options will be auto-formatted with labels for usage with forms and filters when sessions are enabled via AirLock or otherwise.

```
"options": {
	"orderable": [
		{
			"value": "my_field" 
			"label": "My Field"
		},
	]
	"sort": [
		{
			"value": "asc" 
			"label": "Asc"
		},
	]
}

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
                    query: Object.assign({}, this.$route.query, {
                        page: this.page - 1
                    })
                }, () => this.$emit('prevPage'), () => this.$emit('failed'))
            },
            nextPage() {
                if (this.value.isLastPage) return;
                this.$router.push({
                    query: Object.assign({}, this.$route.query, {
                        page: this.page + 1
                    })
                }, () => this.$emit('nextPage'), () => this.$emit('failed'))
            }
        }
    }
</script>
<template>
    <div class="grid mt-4">
        <div class="grid-item pr-1">
            <button
                id="prevPage"
                dusk="prevPage"
                @click="prevPage"
                :disabled="value.isFirstPage">
                Prev
            </button>
        </div>
        <div class="grid-item pl-1">
            <button
                id="nextPage"
                dusk="nextPage"
                @click="nextPage"
                :disabled="value.isLastPage">
                Next
            </button>
        </div>
        <div v-if="value.current_page && value.last_page" class="grid-item text-xs text-gray-500 hidden sm:block">
            Page <strong>{{ value.current_page }}</strong> of <strong>{{ value.last_page }}</strong>
        </div>
        <div v-if="value.total" class="grid-item text-xs text-gray-500 hidden sm:block">
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

### RoadMap:
* Add methods for appending data.
* Add interface for appending options from queries.
* You decide...

### Testing

``` bash
composer test
composer lint
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email dan@bayareawebpro.com instead of using the issue tracker.

## Credits

- [Daniel Alvidrez](https://github.com/bayareawebpro)
- All Contributors

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
