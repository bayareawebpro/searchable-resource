<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Closure;

trait Whenable{

    /**
     * Whenable true, callback with builder instance.
     * @param bool $condition
     * @param Closure $closure
     * @return $this
     */
    public function when(bool $condition, Closure $closure): self
    {
        if($condition){
            call_user_func($closure, $this);
        }
        return $this;
    }
}
