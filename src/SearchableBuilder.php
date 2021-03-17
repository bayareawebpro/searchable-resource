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

use Illuminate\Validation\ValidationException;
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

    /**
     * @var Paginator|EloquentCollection
     */
    protected $result;

    protected string $resource = JsonResource::class;
    protected bool $labeled = false;
    protected int $paginate;
    protected array $orderable = [];
    protected string $order_by = 'id';
    protected string $sort = 'desc';

    protected array $select = ['*'];
    protected array $appendable = [];
    protected array $validated = [];
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
        return $this->query->paginate($this->getPerPage(), $this->select)->appends($this->validated);
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
     * Get the options for queries.
     * @return Collection
     */
    protected function buildOptions(): Collection
    {
        if ($this->labeled) {

            $options = Collection::make($this->options)
                ->map(fn($options, $key) => $this->formatOptions($key, Collection::make($options))->unique()->all())
                ->all();

            $options = Collection::make(array_merge([
                'order_by' => $this->formatOptions('order_by', $this->getOrderableOptions())->all(),
                'sort'     => $this->formatOptions('sort', $this->getSortOptions())->all(),
                'per_page' => $this->formatOptions('per_page', $this->getPerPageOptions())->all(),
            ], $options));
        }else{

            $options = Collection::make(array_merge([
                'order_by' => $this->getOrderableOptions()->all(),
                'sort'     => $this->getSortOptions()->all(),
                'per_page' => $this->getPerPageOptions()->all(),
            ], $this->options));
        }

        if(!$this->shouldPaginate()){
            return $options->forget('per_page');
        }
        return $options;
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
     * Get Additional Data for Paginated Queries
     * @param Paginator $paginator
     * @return array
     */
    protected function getPaginatedAdditional(Paginator $paginator): array
    {
        return array_merge([
            'pagination' => $this->formatPaginator($paginator),
            'query'      => $this->formatQuery($paginator),
            'options'    => $this->buildOptions(),
        ], $this->with);
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
     * Get the options collection.
     */
    public function getOptions(): Collection
    {
        return $this->buildOptions();
    }

    /**
     * Get the options collection.
     */
    public function getPage(): int
    {
        return data_get($this->validated, 'page', 1);
    }

    /**
     * Get Search Parameter
     */
    public function getSearch(): ?string
    {
        return data_get($this->validated, 'search');
    }

    /**
     * Execute Query and Gather Items.
     * @throws \Illuminate\Validation\ValidationException
     * @return $this
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
     * Get the response representation of the data.
     * @param Request|null $request
     * @throws \Illuminate\Validation\ValidationException
     * @return JsonResponse
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
     * @throws \Illuminate\Validation\ValidationException
     * @return array
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
                    $query->set('parameterBag', Arr::only($this->validated, $query->getFields()));
                    $this->query->tap($query);
                    $this->fields($query->getFields());
                }
            } else {
                $query->set('parameterBag', Arr::only($this->validated, $query->getFields()));
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
            $this->withOptions($query->getOptions());
        });
    }

    /**
     * Validate the request.
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRequest(): void
    {
        $validator = $this->validator->make($this->request->all(), $this->compileRules());

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->messages())->redirectTo($this->request->path());
        }

        $this->validated = $validator->validated();
    }

    /**
     * Should the response be paginated.
     */
    protected function shouldPaginate(): bool
    {
        return isset($this->paginate);
    }
}
