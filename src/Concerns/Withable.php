<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Withable{

    /**
     * With additional data.
     * @param array $data
     * @return $this
     */
    public function with(array $data): self
    {
        $this->with = array_merge($this->with, $data);
        return $this;
    }
}
