<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use BayAreaWebPro\SearchableResource\Contracts\ProvidesOptions;
use BayAreaWebPro\SearchableResource\AbstractQuery;

class MockOptionsQuery extends AbstractQuery implements ProvidesOptions
{
    protected string $field = 'option';

    public function options(): array
    {
        return [
            $this->getField() => [
                'my_option'
            ],
        ];
    }
}
