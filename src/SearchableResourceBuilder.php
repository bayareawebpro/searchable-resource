<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\{
    Request, JsonResponse
};
use Illuminate\Database\Eloquent\{
    Collection as EloquentCollection, Builder
};

use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Pagination\Paginator;

use BayAreaWebPro\SearchableResource\Contracts\{
    ConditionalQuery, ValidatableQuery, ProvidesOptions
};

use BayAreaWebPro\SearchableResource\Concerns\{
    Resourceful,
    Validatable,
    Appendable,
    Orderable,
    Paginated,
    Selectable,
    Sortable,
    Labeled,
    Optional,
    Withable
};

class SearchableResourceBuilder implements Responsable
{
    use Resourceful;
    use Validatable;
    use Appendable;
    use Selectable;
    use Orderable;
    use Paginated;
    use Sortable;
    use Optional;
    use Withable;
    use Labeled;

    protected Request $request;
    protected Builder $query;

    /**
     * Default Pagination Count
     * @var int
     */
    protected int $paginate;

    /**
     * Allowed Orderable Attributes
     * @var array
     */
    protected array $orderable = [];

    /**
     * Default Orderable Attribute
     * @var string
     */
    protected string $order_by = 'id';

    /**
     * Default Sort Direction
     * @var string
     */
    protected string $sort = 'desc';

    /**
     * Should use Labels
     * @var bool
     */
    protected bool $labeled = false;

    /**
     * Appendable Attributes
     * @var array
     */
    protected array $appendable = [];

    /**
     * Select Columns
     * @var array
     */
    protected array $select = ['*'];

    /**
     * Allowed Orderable Attributes
     * @var string
     */
    protected string $resource = JsonResource::class;

    /**
     * With Additional Data in Response
     * @var array
     */
    protected array $with = [];

    /**
     * Additional Options
     * @var array
     */
    protected array $options = [];

    /**
     * Request Fields Included in Response Query State.
     * @var array
     */
    protected array $fields = [];

    /**
     * Validation Rules.
     * @var array
     */
    protected array $rules = [];

    /**
     * SearchableResource constructor.
     * @param Request $request
     * @param Builder $query
     */
    public function __construct(Request $request, Builder $query)
    {
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
            } elseif (class_exists($query) && is_subclass_of($query, AbstractQuery::class)) {
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
    public function query(AbstractQuery $query)
    {
        $this->withFields([$query->getField()]);
        if ($query instanceof ConditionalQuery) {
            $this->query->when($query->applies(), $query);
        } else {
            $this->query->tap($query);
        }
        if ($query instanceof ValidatableQuery) {
            foreach ($query->rules() as $rule) {
                $this->withRules($rule);
            }
        }
        if ($query instanceof ProvidesOptions) {
            $this->options($query->getOptions());
        }
        return $this;
    }

    /**
     * Execute queries and get the paginator instance.
     * @return Paginator
     */
    protected function executePaginatorQuery(): Paginator
    {
        $this->request->validate($this->rules());
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->paginate($this->getPerPage(), $this->select);
    }

    /**
     * Execute queries and get the paginator instance.
     * @return EloquentCollection
     */
    protected function executeQuery(): EloquentCollection
    {
        $this->request->validate($this->rules());
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->get($this->select);
    }

    /**
     * Get the options for queries.
     * @return Collection
     */
    protected function getOptions(): Collection
    {
        /**
         * Formatted label / value assoc. arrays
         */
        if ($this->labeled) {
            return Collection::make(array_merge($this->options, [
                'orderable' => $this->formatOrderableOptions()->all(),
                'per_page'  => $this->formatPerPageOptions()->all(),
                'sort'      => $this->formatSortOptions()->all(),
            ]));
        }

        /**
         * Raw values arrays
         */
        return Collection::make(array_merge($this->options, [
            'orderable' => $this->getOrderableOptions()->all(),
            'per_page'  => $this->getPerPageOptions()->all(),
            'sort'      => $this->getSortOptions()->all(),
        ]));
    }

    /**
     * Get the response representation of the data.
     * @param Request|null $request
     * @return JsonResponse
     */
    public function toResponse($request = null): Response
    {
        $this->request = $request ?: $this->request;

        if (isset($this->paginate)) {
            return $this->formatPaginatedResponse($request);
        }

        return $this->formatResponse($request);
    }

    /**
     * @param Request|null $request
     * @return JsonResponse
     */
    protected function formatPaginatedResponse($request = null): JsonResponse
    {
        $paginator = $this->executePaginatorQuery();
        $items = $this->appendAppendable($paginator->items());

        return $this->resource::collection($items)
            ->additional(
                array_merge([
                    'pagination' => $this->formatPaginator($paginator),
                    'query'      => $this->formatQuery($paginator),
                    'options'    => $this->getOptions(),
                ], $this->with)
            )->toResponse($request);
    }

    /**
     * @param Request|null $request
     * @return JsonResponse
     */
    protected function formatResponse($request = null): JsonResponse
    {
        $items = $this->appendAppendable($this->executeQuery()->all());

        return $this->resource::collection($items)->additional(
            array_merge([
                'query'   => $this->formatQuery(),
                'options' => $this->getOptions()->forget('per_page'),
            ], $this->with)
        )->toResponse($request);
    }
}
