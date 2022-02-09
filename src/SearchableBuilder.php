<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\{
    Request,
    Resources\Json\JsonResource
};
use Illuminate\Database\Eloquent\{
    Collection as EloquentCollection,
    Builder
};
use Illuminate\Support\{
    Arr,
    Collection,
    Traits\Macroable
};
use Illuminate\Contracts\{
    Validation\Factory as Validator,
    Support\Arrayable,
    Support\Responsable,
    Pagination\Paginator
};
use BayAreaWebPro\SearchableResource\Contracts\{
    ValidatableQuery,
    ConditionalQuery,
    ProvidesOptions,
    FormatsOptions
};
use BayAreaWebPro\SearchableResource\Concerns\{
    Resourceful,
    Validatable,
    Appendable,
    Selectable,
    Orderable,
    Paginated,
    Sortable,
    Optional,
    Labeled,
    Withable,
    Whenable
};

use Symfony\Component\HttpFoundation\Response;

class SearchableBuilder implements Responsable, Arrayable
{
    use Macroable;
    use Resourceful;
    use Validatable;
    use Appendable;
    use Selectable;
    use Orderable;
    use Paginated;
    use Sortable;
    use Optional;
    use Withable;
    use Whenable;
    use Labeled;

    protected string $resource = GenericResource::class;
    protected int $paginate;
    protected bool $labeled = false;
    protected array $orderable = [];
    protected string $order_by = 'id';
    protected string $sort = 'desc';

    protected array $select = ['*'];
    protected array $appendable = [];
    protected array $parameters = [];
    protected array $options = [];
    protected array $fields = [];
    protected array $rules = [];
    protected array $with = [];

    protected FormatsOptions $formatter;
    protected Collection $queries;
    protected Validator $validator;
    protected Request $request;
    protected Builder $query;

    /**
     * @var Paginator|EloquentCollection
     */
    protected $result;

    public function __construct(Request $request, Validator $validator, Builder $query)
    {
        $this->queries = Collection::make();
        $this->validator = $validator;
        $this->request = $request;
        $this->query = $query;
    }

    public static function make(Builder $query): self
    {
        return app(static::class, [
            'query' => $query,
        ]);
    }

    public function queries(array $queries): self
    {
        foreach ($queries as $query) {
            if ($query instanceof AbstractQuery) {
                $this->query($query);
            } elseif (is_string($query) && is_subclass_of($query, AbstractQuery::class)) {
                $this->query(app($query));
            }
        }
        return $this;
    }

    public function query(AbstractQuery $query): self
    {
        $this->queries->push($query);
        return $this;
    }

    protected function executePaginatorQuery(): Paginator
    {
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->paginate($this->getPerPage(), $this->select)
            ->appends($this->parameters)
            ->onEachSide(1);
    }

    protected function executeQuery(): EloquentCollection
    {
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->get($this->select);
    }

    protected function formatPaginatedResource(): JsonResource
    {
        return $this->resource::collection($this->appendAppendable($this->result->items()))
            ->additional($this->getPaginatedAdditional($this->result));
    }

    protected function formatBaseResource(): JsonResource
    {
        return $this->resource::collection($this->appendAppendable($this->result->all()))
            ->additional($this->getBaseAdditional());
    }

    protected function getBaseAdditional(): array
    {
        return array_merge([
            'query'   => $this->formatQuery(),
            'options' => $this->buildOptions(),
        ], $this->with);
    }

    /**
     * @return Paginator|EloquentCollection
     */
    public function getItems()
    {
        return $this->result;
    }

    public function params(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        foreach ($parameters as $field => $value){
            if(!$this->request->has($field)){
                $this->request->merge([$field => $value]);
            }
        }
        return $this;
    }

    protected function fields(array $requestFields): self
    {
        $this->fields = array_merge($this->fields, $requestFields);
        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->getParameter('search');
    }

    public function execute(): self
    {
        $this->compileQueryRules();
        $this->validateRequest();
        $this->compileQueryStatements();
        $this->result = ($this->shouldPaginate() ? $this->executePaginatorQuery() : $this->executeQuery());
        $this->compileQueryOptions();
        return $this;
    }

    protected function compileQueryRules(): void
    {
        $this->queries->each(function (AbstractQuery $query) {
            if ($query instanceof ValidatableQuery) {
                if ($query instanceof ConditionalQuery) {
                    if ($query->getApplies()) {
                        $this->rules($query->getRules());
                    }
                } else {
                    $this->rules($query->getRules());
                }
            }
        });
    }

    protected function compileQueryStatements(): void
    {
        $this->queries->each(function (AbstractQuery $query) {
            if ($query instanceof ConditionalQuery) {
                if ($query->getApplies()) {
                    $query->set('parameterBag', Arr::only($this->parameters, $query->getFields()));
                    $this->query->tap($query);
                    $this->fields($query->getFields());
                }
            } else {
                $query->set('parameterBag', Arr::only($this->parameters, $query->getFields()));
                $this->query->tap($query);
                $this->fields($query->getFields());
            }
        });
    }

    protected function compileQueryOptions(): void
    {
        $this->queries->whereInstanceOf(ProvidesOptions::class)->each(function (ProvidesOptions $query) {
            $this->options($query->getOptions());
        });
    }

    public function toResponse($request = null): Response
    {
        $this->request = $request ?: $this->request;

        $this->execute();

        if ($this->shouldPaginate()) {
            return $this
                ->formatPaginatedResource()
                ->toResponse($this->request);
        }

        return $this
            ->formatBaseResource()
            ->toResponse($this->request);
    }

    public function toArray()
    {
        $this->execute();

        if ($this->shouldPaginate()) {
            return array_merge([
                'data' => $this->appendAppendable($this->result->items()),
            ], $this->getPaginatedAdditional($this->result), $this->with);
        }

        return array_merge([
            'data' => $this->appendAppendable($this->result->all()),
        ], $this->getBaseAdditional(), $this->with);
    }
}
