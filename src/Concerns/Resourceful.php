<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;
use Illuminate\Http\Resources\Json\JsonResource;

trait Resourceful{

    /**
     * Use Resource
     * @param string $resource
     * @return $this
     */
    public function resource(string $resource): self
    {
        if(class_exists($resource) && is_subclass_of($resource, JsonResource::class)){
            $this->resource = $resource;
        }
        return $this;
    }
}
