<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

trait Whenable{

    /**
     * When true, callback with builder instance.
     * @param bool $condition
     * @param $callback
     * @return $this
     */
    public function when($condition, $callback): self
    {
        if($condition){
            return $this->tap($callback);
        }
        return $this;
    }

    /**
     * When true, callback with builder instance.
     * @param $callback
     * @return $this
     */
    public function tap($callback): self
    {
        if(is_callable($callback)){
            call_user_func($callback, $this);
        }
        return $this;
    }
}
