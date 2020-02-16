<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;


use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Paginated{

    /**
     * Set default paginator count.
     * @param int $paginate
     * @return $this
     */
    public function paginate(int $paginate): self
    {
        $this->paginate = $paginate;
        return $this;
    }

    /**
     * Get the current paginator count.
     * @return int
     */
    public function getPerPage(): int
    {
        return (int) ($this->request->get('per_page') ?? $this->paginate);
    }

    /**
     * Get the ordered attribute name.
     * @return Collection
     */
    public function getPerPageOptions(): Collection
    {
        return Collection::make(config('searchable-resource.per_page_options', []));
    }

    /**
     * Get per-page options.
     * @return Collection
     */
    protected function formatPerPageOptions(): Collection
    {
        return $this->getPerPageOptions()->map(fn($entry) => [
            'label' => Str::title("$entry / page"),
            'value' => $entry,
        ]);
    }

    /**
     * Format the paginator attributes.
     * @param Paginator $paginator
     * @return array
     */
    protected function formatPaginator(Paginator $paginator): array
    {
        $params = array_merge(Arr::except($paginator->toArray(), ['data']), [
            'isFirstPage' => $paginator->currentPage() === 1,
            'isLastPage' => null
        ]);

        if($paginator instanceof LengthAwarePaginator){
            return array_merge($params, [
                'isLastPage'  => $paginator->currentPage() === $paginator->lastPage(),
            ]);
        }

        return $params;
    }

    /**
     * Format the query attributes.
     * @param Paginator|null $paginator
     * @return array
     */
    protected function formatQuery(?Paginator $paginator = null): array
    {
        $params = array_merge($this->request->only($this->fields), [
            'order_by' => $this->getOrderBy(),
            'sort'     => $this->getSort(),
        ]);

        if($paginator){
            $params = array_merge($params, [
                'page'     => $paginator->currentPage(),
                'per_page' => $this->getPerPage(),
            ]);
        }

        if($this->request->filled('search')){
            $params = array_merge($params, [
                'search'   => $this->request->get('search'),
            ]);
        }

        return $params;
    }
}
