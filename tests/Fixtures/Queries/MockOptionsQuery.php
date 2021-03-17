<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries;

use BayAreaWebPro\SearchableResource\Contracts\ProvidesOptions;
use BayAreaWebPro\SearchableResource\AbstractQuery;

class MockOptionsQuery extends AbstractQuery implements ProvidesOptions
{
    public string $field = 'option';

    public function getOptions(): array
    {
        return [
            'untouched' => [
                [
                    'label' => 'Value 1',
                    'value' => 'value1',
                ],
                [
                    'label' => 'Value 2',
                    'value' => 'value2',
                ],
            ],
            $this->field => [
                'my_option'
            ],
        ];
    }
}
