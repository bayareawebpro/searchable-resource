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

    /**
     * With fields from the request appended to the query.
     * @param array $requestFields
     * @return $this
     */
    public function withFields(array $requestFields): self
    {
        $this->fields = array_merge($this->fields, $requestFields);
        return $this;
    }
}
