<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Labeled{

    /**
     * Use labels for options.
     * @param bool $enabled
     * @return $this
     */
    public function labeled(bool $enabled = true): self
    {
        $this->labeled = $enabled;
        return $this;
    }
}
