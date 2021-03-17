<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\AbstractQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;

class MockRoleQuery extends AbstractQuery implements ConditionalQuery, ValidatableQuery
{
    public string $field = 'role';
    protected string $attribute = 'role';

    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, $this->getValue());
    }

    public function getRules(): array
    {
        return [
            $this->field => 'sometimes|string|in:admin,editor,guest',
        ];
    }
}
