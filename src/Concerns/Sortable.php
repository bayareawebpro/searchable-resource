<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Sortable{
    public function sort(string $sort): self
    {
        $this->sort = $sort;
        return $this;
    }
    public function getSort(): string
    {
        return $this->getParameter('sort',$this->sort);
    }
    protected function getSortOptions(): Collection
    {
        return Collection::make([
            'asc',
            'desc'
        ]);
    }
}
