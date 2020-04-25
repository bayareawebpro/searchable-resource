<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Support\Collection;

trait Orderable{

    /**
     * Add orderable attributes.
     * @param array $orderable
     * @return $this
     */
    public function orderable(array $orderable): self
    {
        $this->orderable = array_merge($this->orderable, $orderable);
        return $this;
    }

    /**
     * Set default orderBy attribute.
     * @param string $orderBy
     * @return $this
     */
    public function orderBy(string $orderBy): self
    {
        $this->order_by = $orderBy;
        return $this;
    }

    /**
     * Get the ordered attribute name.
     * @return string
     */
    public function getOrderBy(): string
    {
        return (string) ($this->request->get('order_by') ?? $this->order_by);
    }

    /**
     * Get the ordered attribute name.
     * @return Collection
     */
    public function getOrderableOptions(): Collection
    {
        return Collection::make($this->orderable);
    }
}
