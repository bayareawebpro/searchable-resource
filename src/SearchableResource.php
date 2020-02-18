<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \BayAreaWebPro\SearchableResource\SearchableBuilder
 */
class SearchableResource extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'searchable-resource';
    }
}
