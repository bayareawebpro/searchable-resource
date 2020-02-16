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
<?php
use App\User;
use BayAreaWebPro\SearchableResource\SearchableResource;

class MyController{

	public function index()
	{
		return SearchableResource::make(User::query())->paginate(16);
	}
}
```


#### Validation

Validation is compiled from your queries and options.

#### Ordering and Sorting

The default settings:

* OrderBy ID
* DESC

The orderable options are automatically validated.  You can specify as many columns as you wish.  Values be compatible with your query.

```php
SearchableResource::make(User::query())
	->orderBy('name')
	->sort('desc')
	->orderable([
		'name'
	])
;
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

Queries can also be added by instantiating each query using the make method.  This can be useful when you need more methods and logic to determine usage.

For example let's say we have a select field query that implments it's own builder interface:

```php
use App\Queries\SelectQuery;

$searchable = SearchableResource::make(User::query());

$searchable->query(
	SelectQuery::make($request)
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

SearchableResources are generic resources by default.  You can easily specify which resource class should be used to map your models.

> Must extend `JsonResource`.

```php
SearchableResource::make(User::query())
	->resource(UserResource::class)
	->paginate(8)
```

### Response Output

The relevant query parameters and request options are appended to the output for convenience.  Two additional properties have been added to the pagination parameters to remove the need for conditionals on the client / user side. `isFirstPage` and `isLastPage` makeing pagination buttons easy to disable via props (Vue | React).

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
	]
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
