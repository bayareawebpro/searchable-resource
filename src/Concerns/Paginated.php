<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

trait Paginated
{
    protected function shouldPaginate(): bool
    {
        return isset($this->paginate);
    }

    public function paginate(int $paginate): self
    {
        $this->paginate = $paginate;
        return $this;
    }

    public function getPage(): int
    {
        return $this->getParameter('page', 1);
    }

    public function getPerPage(): int
    {
        return (int)$this->getParameter('per_page', $this->paginate);
    }

    public function getPerPageOptions(): Collection
    {
        return Collection::make(config('searchable-resource.per_page_options', []));
    }

    protected function formatPaginator(Paginator $paginator): array
    {
        $params = array_merge(Arr::except($paginator->toArray(), ['data']), [
            'isFirstPage' => $paginator->currentPage() === 1,
            'isLastPage'  => null
        ]);

        if ($paginator instanceof LengthAwarePaginator) {
            return array_merge($params, [
                'isLastPage' => $paginator->currentPage() === $paginator->lastPage(),
            ]);
        }

        return $params;
    }

    protected function formatQuery(?Paginator $paginator = null): array
    {
        $params = array_merge($this->parameters, $this->request->only($this->fields), [
            'order_by' => $this->getOrderBy(),
            'sort'     => $this->getSort(),
            'search'   => $this->getSearch()
        ]);

        if ($paginator) {
            $params = array_merge($params, [
                'page'     => $paginator->currentPage(),
                'per_page' => $this->getPerPage(),
            ]);
        }

        return $params;
    }

    protected function getPaginatedAdditional(Paginator $paginator): array
    {
        return array_merge([
            'pagination' => $this->formatPaginator($paginator),
            'query'      => $this->formatQuery($paginator),
            'options'    => $this->buildOptions(),
        ], $this->with);
    }
}
