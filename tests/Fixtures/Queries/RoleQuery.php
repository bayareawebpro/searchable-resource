<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;

class RoleQuery extends AbstractQuery implements ConditionalQuery, ValidatableQuery
{
    protected string $field = 'role';

    public function __invoke(Builder $builder): void
    {
        $builder->where('role', $this->getValue());
    }

    public function rules(): array
    {
        return [
            [$this->field => 'sometimes|string|in:admin,editor,guest'],
        ];
    }
}
