<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Collection;

trait Orderable
{
    public function orderable(array $orderable): self
    {
        $this->orderable = array_merge($this->orderable, $orderable);
        return $this;
    }

    public function orderBy(string $orderBy): self
    {
        $this->order_by = $orderBy;
        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->getParameter('order_by', $this->order_by);
    }

    public function getOrderableOptions(): Collection
    {
        return Collection::make($this->orderable);
    }
}
