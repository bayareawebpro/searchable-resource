<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use BayAreaWebPro\SearchableResource\Contracts\FormatsOptions;
use BayAreaWebPro\SearchableResource\OptionsFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Optional
{

    /**
     * With additional options.
     * @param array $additional
     * @return $this
     */
    public function withOptions(array $additional): self
    {
        $this->options = array_merge($this->options, $additional);
        return $this;
    }

    /**
     * @param FormatsOptions $instance
     * @return $this
     */
    public function useFormatter(FormatsOptions $instance)
    {
        $this->formatter = $instance;
        return $this;
    }

    /**
     * Format Options
     * @param string $key
     * @param Collection $options
     * @return Collection
     */
    protected function formatOptions(string $key, Collection $options): Collection
    {
        if(isset($this->formatter)){
            return app()->call($this->formatter, compact('key','options'));
        }
        return app()->call(new OptionsFormatter, compact('key','options'));
    }
}
