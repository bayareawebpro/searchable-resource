<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\Contracts\InvokableQuery;

abstract class AbstractQuery implements InvokableQuery
{
    protected Request $request;

    /**
     * The request field name.
     * @var string
     */
    protected string $field = '';

    /**
     * The model attribute name.
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
    public function applies(): bool
    {
        return $this->request->filled($this->field);
    }

    /**
     * Validatable Query
     * @return array
     */
    public function rules(): array
    {
        return [
            $this->field => ['required'],
        ];
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
     * Get the field name.
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the attribute name.
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
