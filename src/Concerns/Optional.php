<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Optional{

    /**
     * With additional options.
     * @param array $additional
     * @return $this
     */
    public function options(array $additional): self
    {
        $this->options = array_merge($this->options, $additional);
        return $this;
    }
}
