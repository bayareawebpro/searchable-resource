<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Selectable{

    /**
     * Select Columns
     * @param array $columns
     * @return $this
     */
    public function select(array $columns = ['*']): self
    {
        $this->select = array_merge($this->select, $columns);
        return $this;
    }
}
