<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use BayAreaWebPro\SearchableResource\Contracts\FormatsOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OptionsFormatter implements FormatsOptions
{
    /**
     * @param string $key
     * @param Collection $options
     * @return Collection
     */
    public function __invoke(string $key, Collection $options): Collection
    {
        return $this->baseOptions($key, $options);
    }

    /**
     * @param string $key
     * @param Collection $options
     * @return Collection
     */
    protected function baseOptions(string $key, Collection $options): Collection
    {
        if(is_array($options->first())){
            return $options;
        }
        if (in_array($key, ['sort', 'order_by'])) {
            return $this->titleCase($options);
        }
        if ($key === 'per_page') {
            return $this->append($options, "/ Page");
        }
        return $this->literal($options);
    }

    /**
     * @param Collection $options
     * @param string $append
     * @return Collection
     */
    protected function append(Collection $options, string $append): Collection
    {
        return $options->map(fn($value, $key) => [
            'label' => "$value $append",
            'value' => $value,
        ]);
    }

    /**
     * @param Collection $options
     * @param string $label
     * @return Collection
     */
    protected function nullable(Collection $options, string $label = 'All'): Collection
    {
        return $options->prepend([
            'label' => $label,
            'value' => null,
        ]);
    }

    /**
     * @param Collection $options
     * @return Collection
     */
    protected function titleCase(Collection $options): Collection
    {
        return $options->map(fn($value, $key) => [
            'label' => Str::title(str_replace("_", " ", "$value")),
            'value' => $value,
        ]);
    }

    /**
     * @param Collection $options
     * @return Collection
     */
    protected function literal(Collection $options): Collection
    {
        return $options->map(fn($value, $key) => [
            'label' => $value,
            'value' => $value,
        ]);
    }
}
