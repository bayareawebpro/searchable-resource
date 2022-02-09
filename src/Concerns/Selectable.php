<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Selectable{
    public function select(array $columns = ['*']): self
    {
        $this->select = $columns;
        return $this;
    }
}
