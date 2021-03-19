<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\{
    Request,
    JsonResponse,
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

    protected string $resource = JsonResource::class;
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

    /**
     * SearchableResource constructor.
     * @param Request $request
     * @param Builder $query
     */
    public function __construct(Request $request, Validator $validator, Builder $query)
    {
        $this->queries = Collection::make();
        $this->validator = $validator;
        $this->request = $request;
        $this->query = $query;
    }

    /**
     * Static Make Method
     * @param Builder $query
     * @return static
     */
    public static function make(Builder $query): self
    {
        return app(static::class, [
            'query' => $query,
        ]);
    }

    /**
     * Apply Invokable Queries
     * @param array $queries
     * @return $this
     */
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

    /**
     * Add an Invokable Query.
     * @param AbstractQuery $query
     * @return $this
     */
    public function query(AbstractQuery $query): self
    {
        $this->queries->push($query);
        return $this;
    }

    /**
     * Execute queries and get the paginator instance.
     */
    protected function executePaginatorQuery(): Paginator
    {
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->paginate($this->getPerPage(), $this->select)
            ->appends($this->parameters)
            ->onEachSide(1);
    }

    /**
     * Execute queries and get the paginator instance.
     */
    protected function executeQuery(): EloquentCollection
    {
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->get($this->select);
    }

    /**
     * Format the paginated resource.
     */
    protected function formatPaginatedResource(): JsonResource
    {
        return $this->resource::collection($this->appendAppendable($this->result->items()))
            ->additional($this->getPaginatedAdditional($this->result));
    }

    /**
     * Format the base resource.
     */
    protected function formatBaseResource(): JsonResource
    {
        return $this->resource::collection($this->appendAppendable($this->result->all()))
            ->additional($this->getBaseAdditional());
    }

    /**
     * Get Additional Data for Base Queries
     */
    protected function getBaseAdditional(): array
    {
        return array_merge([
            'query'   => $this->formatQuery(),
            'options' => $this->buildOptions(),
        ], $this->with);
    }

    /**
     * Get the result collection or paginator.
     * @return Paginator|EloquentCollection
     */
    public function getItems()
    {
        return $this->result;
    }

    /**
     * Get Search Parameter value.
     * @param array $query
     * @return SearchableBuilder
     */
    public function params(array $query): self
    {
        $this->parameters = array_merge($this->parameters, $query);
        return $this;
    }

    /**
     * With fields from the request appended to the query.
     * @param array $requestFields
     * @return $this
     */
    protected function fields(array $requestFields): self
    {
        $this->fields = array_merge($this->fields, $requestFields);
        return $this;
    }

    /**
     * Get Search Parameter value.
     */
    public function getSearch(): ?string
    {
        return $this->getParameter('search');
    }

    /**
     * Execute Query and Gather Items.
     * @return $this
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(): self
    {
        $this->compileQueryRules();
        $this->validateRequest();
        $this->compileQueryStatements();
        $this->result = ($this->shouldPaginate() ? $this->executePaginatorQuery() : $this->executeQuery());
        $this->compileQueryOptions();
        return $this;
    }

    /**
     * Compile & Apply Query Rules
     */
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

    /**
     * Compile & Apply Query Statements
     */
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

    /**
     * Compile Query Options
     */
    protected function compileQueryOptions(): void
    {
        $this->queries->whereInstanceOf(ProvidesOptions::class)->each(function (ProvidesOptions $query) {
            $this->options($query->getOptions());
        });
    }

    /**
     * Get the response representation of the data.
     * @param Request|null $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * Get the array representation of the data.
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
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
