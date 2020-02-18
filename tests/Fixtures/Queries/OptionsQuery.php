<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use BayAreaWebPro\SearchableResource\Contracts\ProvidesOptions;
use BayAreaWebPro\SearchableResource\AbstractQuery;

class OptionsQuery extends AbstractQuery implements ProvidesOptions
{
    protected string $field = 'search';

    public function options(): array
    {
        return [
            'key' => 'value',
        ];
    }
}
