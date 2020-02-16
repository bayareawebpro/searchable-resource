<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface InvokableQuery
{
    public function __invoke(Builder $builder): void;
    public function getField(): string;
}
