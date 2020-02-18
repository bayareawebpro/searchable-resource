<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

use BayAreaWebPro\SearchableResource\SearchableBuilder;

interface InvokableBuilder
{
    public function __invoke(SearchableBuilder $builder): void;
}
