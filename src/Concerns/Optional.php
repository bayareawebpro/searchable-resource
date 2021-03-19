<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use BayAreaWebPro\SearchableResource\Contracts\FormatsOptions;
use BayAreaWebPro\SearchableResource\OptionsFormatter;
use Illuminate\Support\Collection;

trait Optional
{

    /**
     * Get the options collection.
     */
    public function getOptions(): Collection
    {
        return $this->buildOptions();
    }
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

    /**
     * Get the options for queries.
     * @return Collection
     */
    protected function buildOptions(): Collection
    {
        if ($this->labeled) {

            $options = Collection::make($this->options)
                ->map(fn($options, $key) => $this->formatOptions($key, Collection::make($options))->unique()->all())
                ->all();

            $options = Collection::make(array_merge([
                'order_by' => $this->formatOptions('order_by', $this->getOrderableOptions())->all(),
                'sort'     => $this->formatOptions('sort', $this->getSortOptions())->all(),
                'per_page' => $this->formatOptions('per_page', $this->getPerPageOptions())->all(),
            ], $options));
        }else{

            $options = Collection::make(array_merge([
                'order_by' => $this->getOrderableOptions()->all(),
                'sort'     => $this->getSortOptions()->all(),
                'per_page' => $this->getPerPageOptions()->all(),
            ], $this->options));
        }

        if(!$this->shouldPaginate()){
            return $options->forget('per_page');
        }
        return $options;
    }
}
