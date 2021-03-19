<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Get the current page value.
     */
    public function getPage(): int
    {
        return $this->getParameter('page', 1);
    }

    /**
     * Get the current paginator count.
     * @return int
     */
    public function getPerPage(): int
    {
        return (int)$this->getParameter('per_page',$this->paginate);
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
        $params = array_merge($this->parameters, $this->request->only($this->fields), [
            'order_by' => $this->getOrderBy(),
            'sort'     => $this->getSort(),
            'search'   => $this->getSearch()
        ]);

        if($paginator){
            $params = array_merge($params, [
                'page'     => $paginator->currentPage(),
                'per_page' => $this->getPerPage(),
            ]);
        }

        return $params;
    }

    /**
     * Get Additional Data for Paginated Queries
     * @param Paginator $paginator
     * @return array
     */
    protected function getPaginatedAdditional(Paginator $paginator): array
    {
        return array_merge([
            'pagination' => $this->formatPaginator($paginator),
            'query'      => $this->formatQuery($paginator),
            'options'    => $this->buildOptions(),
        ], $this->with);
    }

    /**
     * Should the response be paginated.
     */
    protected function shouldPaginate(): bool
    {
        return isset($this->paginate);
    }
}
