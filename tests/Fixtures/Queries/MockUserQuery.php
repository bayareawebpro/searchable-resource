<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;

class MockUserQuery extends AbstractQuery implements ConditionalQuery, ValidatableQuery
{
    public string $field = 'search';

    public function __invoke(Builder $builder): void
    {
        $value = $this->getValue($this->field);
        $builder->where(fn(Builder $builder) => $builder
            ->where('name', 'like', "%$value%")
            ->orWhere('email', 'like', "%$value%")
        );
    }

    public function getRules(): array
    {
        return [
            $this->field => 'sometimes|string|max:255',
        ];
    }
}
