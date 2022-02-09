<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use BayAreaWebPro\SearchableResource\Contracts\InvokableBuilder;
use Closure;

trait Whenable
{
    public function when($condition, $callback): self
    {
        if ($condition) {
            return $this->tap($callback);
        }
        return $this;
    }

    public function tap($callback): self
    {
        if (is_callable($callback)) {
            call_user_func($callback, $this);
        }
        return $this;
    }
}
