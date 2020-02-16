<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;

class RoleQuery extends AbstractQuery implements ConditionalQuery
{
    protected string $field = 'role';

    public function __invoke(Builder $builder): void
    {
        $builder->where('role', $this->getValue());
    }

    public function rules(?Request $request = null): array
    {
        return [
            [$this->field => 'sometimes|string|in:admin,editor,guest'],
        ];
    }
}
