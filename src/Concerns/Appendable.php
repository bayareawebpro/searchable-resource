<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Appendable{

    /**
     * Add appendable attributes.
     * @param array $appendable
     * @return $this
     */
    public function appendable(array $appendable): self
    {
        $this->appendable = array_merge($this->appendable, $appendable);
        return $this;
    }

    /**
     * Append model attributes.
     * @param array $items
     * @return array
     */
    protected function appendAppendable(array $items): array
    {
        if (count($this->appendable)) {
            foreach ($items as $entry) {
                $entry->append($this->appendable);
            }
        }
        return $items;
    }
}
