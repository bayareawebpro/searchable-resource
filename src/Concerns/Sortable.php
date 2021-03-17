<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Sortable{

    /**
     * Set default sorted attribute.
     * @param string $sort
     * @return $this
     */
    public function sort(string $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Get the current sort direction.
     * @return string
     */
    public function getSort(): string
    {
        return data_get($this->validated, 'sort',$this->sort);
    }


    /**
     * Get sort direction options.
     * @return Collection
     */
    protected function getSortOptions(): Collection
    {
        return Collection::make([
            'asc',
            'desc'
        ]);
    }
}
