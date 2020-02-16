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
        return (string) ($this->request->get('sort') ?? $this->sort);
    }


    /**
     * Get sort direction options.
     * @return Collection
     */
    protected function getSortOptions(): Collection
    {
        return Collection::make(['asc', 'desc']);
    }

    /**
     * Get formatted sort direction options.
     * @return Collection
     */
    protected function formatSortOptions(): Collection
    {
        return $this->getSortOptions()->map(fn($entry) => [
            'label' => Str::title($entry),
            'value' => is_string($entry) ? Str::slug($entry) : $entry,
        ]);
    }
}
