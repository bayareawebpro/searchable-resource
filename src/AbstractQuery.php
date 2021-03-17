<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\Contracts\InvokableQuery;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractQuery implements InvokableQuery
{
    protected Request $request;

    /**
     * The public request field name.
     * @var string
     */
    public string $field = '';

    /**
     * The protected model attribute name.
     * @var string
     */
    protected string $attribute = '';

    /**
     * Static Make Method
     * @return static
     */
    public static function make(): self
    {
        return app(static::class);
    }

    /**
     * AbstractQuery constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Invokable Query
     * @param Builder $builder
     */
    public function __invoke(Builder $builder): void
    {
        $builder->where($this->attribute, $this->getValue());
    }

    /**
     * Conditional Query
     * @return bool
     */
    public function getApplies(): bool
    {
        return $this->request->filled($this->field);
    }

    /**
     * Get the field value.
     * @param null $fallback
     * @return mixed
     */
    public function getValue($fallback = null)
    {
        return $this->request->get($this->field, $fallback);
    }

    /**
     * Get the field(s) name.
     * @return array
     */
    public function getFields(): array
    {
        $reflection = (new ReflectionClass($this));
        return Collection::make($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->map(fn(ReflectionProperty $prop) => $prop->getValue($this))
            ->values()
            ->toArray();
    }
}
