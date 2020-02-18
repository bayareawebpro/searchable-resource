<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

use Illuminate\Support\Collection;

interface FormatsOptions
{
    public function __invoke(string $key, Collection $options): Collection;
}
