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
     * The validated data bag.
     * @var array
     */
    protected array $parameterBag = [];

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
        $fields = Collection::make($this->getFields());

        if($fields->count() > 1){
            $builder->whereIn($this->attribute, $fields->map(fn($field)=>$this->getValue($field)));

        }elseif($fields->count()){
            $builder->where($this->attribute, $this->getValue($fields->first()));
        }
    }

    /**
     * Conditional Query
     * @return bool
     */
    public function getApplies(): bool
    {
        return $this->request->anyFilled($this->getFields());
    }

    /**
     * Get the field value.
     * @param string $key
     * @param null $fallback
     * @return mixed
     */
    public function getValue(string $key, $fallback = null)
    {
        return data_get($this->parameterBag, $key, $fallback);
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

    /**
     * Set a class property.
     * @param string $property
     * @param mixed $value
     * @return self
     */
    public function set(string $property, $value): self
    {
        if(property_exists($this, $property)){
            $this->{$property} = $value;
        }
        return $this;
    }

    /**
     * Set the field name.
     * @param string $name
     * @return self
     */
    public function field(string $name): self
    {
        $this->set('field',$name);
        return $this;
    }

    /**
     * Set the attribute name.
     * @param string $name
     * @return self
     */
    public function attribute(string $name): self
    {
        $this->set('attribute',$name);
        return $this;
    }
}
