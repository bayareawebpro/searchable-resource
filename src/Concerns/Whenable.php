<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Closure;

trait Whenable{

    /**
     * When true, callback with builder instance.
     * @param bool $condition
     * @param Closure $closure
     * @return $this
     */
    public function when(bool $condition, $closure): self
    {
        if($condition && is_callable($closure)){
            call_user_func($closure, $this);
        }
        return $this;
    }

    /**
     * When true, callback with builder instance.
     * @param Closure $closure
     * @return $this
     */
    public function tap($closure): self
    {
        return $this->when(true, $closure);
    }
}
