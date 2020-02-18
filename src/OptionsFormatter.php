<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use BayAreaWebPro\SearchableResource\Contracts\FormatsOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OptionsFormatter implements FormatsOptions {

    public function __invoke(string $key, Collection $options): Collection
    {
        if($key === 'per_page'){
            return $options->map(fn($value, $key)=>[
                'label' => Str::title("$value / Page"),
                'value' => $value,
            ]);
        }
        return $options->map(fn($value, $key)=>[
            'label' => Str::title(str_replace("_", " ", "$value")),
            'value' => $value,
        ]);
    }
}
