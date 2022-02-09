<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\Contracts\InvokableQuery;

abstract class AbstractQuery implements InvokableQuery
{
    public string $field = '';
    protected string $attribute = '';
    protected array $parameterBag = [];
    protected Request $request;

    public static function make(): self
    {
        return app(static::class);
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke(Builder $builder): void
    {
        $fields = Collection::make($this->getFields());

        if ($fields->count() > 1) {
            $builder->whereIn($this->attribute, $fields->map(fn($field) => $this->getValue($field)));

        } elseif ($fields->count()) {
            $builder->where($this->attribute, $this->getValue($fields->first()));
        }
    }

    public function getApplies(): bool
    {
        return $this->request->anyFilled($this->getFields());
    }

    public function getValue(string $key, $fallback = null)
    {
        return data_get($this->parameterBag, $key, $fallback);
    }

    public function getFields(): array
    {
        $reflection = (new ReflectionClass($this));
        return Collection::make($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->map(fn(ReflectionProperty $prop) => $prop->getValue($this))
            ->values()
            ->toArray();
    }

    public function set(string $property, $value): self
    {
        if (property_exists($this, $property)) {
            $this->{$property} = $value;
        }
        return $this;
    }

    public function field(string $name): self
    {
        $this->set('field', $name);
        return $this;
    }

    public function attribute(string $name): self
    {
        $this->set('attribute', $name);
        return $this;
    }
}
