<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Labeled{

    /**
     * Use labels for options.
     * @param bool $enabled
     * @return $this
     */
    public function labeled($enabled = true): self
    {
        $this->shouldUseLabels = $enabled;
        return $this;
    }
}
