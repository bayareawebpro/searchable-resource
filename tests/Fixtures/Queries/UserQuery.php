<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;

class UserQuery extends AbstractQuery implements ConditionalQuery
{
    protected string $field = 'search';

    public function __invoke(Builder $builder): void
    {
        $value = $this->getValue();
        $builder->where(fn(Builder $builder) => $builder
            ->where('name', 'like', "%$value%")
            ->orWhere('email', 'like', "%$value%")
        );
    }

    public function rules(?Request $request = null): array
    {
        return [
            [$this->field => 'sometimes|string|max:255'],
        ];
    }
}
