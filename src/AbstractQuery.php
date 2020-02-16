<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use BayAreaWebPro\SearchableResource\Contracts\InvokableQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;

abstract class AbstractQuery implements InvokableQuery, ValidatableQuery
{
    protected Request $request;
    protected string $field = '';
    protected string $attribute = '';

    /**
     * @param Request|null $request
     * @return static
     */
    public static function make(?Request $request = null): self
    {
        return app(static::class, [
            'request' => $request
        ]);
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

//    /**
//     * Conditional Query
//     * @param Request $request
//     * @return bool
//     */
//    public function applies(Request $request): bool
//    {
//        return $request->filled($this->field);
//    }

    /**
     * Validatable Query
     * @param Request|null $request
     * @return array
     */
    public function rules(?Request $request = null): array
    {
        return [
            //[$this->field => ['required']],
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
