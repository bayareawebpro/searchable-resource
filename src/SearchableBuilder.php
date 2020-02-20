<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Pagination\Paginator;

use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;
use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use BayAreaWebPro\SearchableResource\Contracts\ProvidesOptions;
use BayAreaWebPro\SearchableResource\Contracts\FormatsOptions;

use BayAreaWebPro\SearchableResource\Concerns\Resourceful;
use BayAreaWebPro\SearchableResource\Concerns\Validatable;
use BayAreaWebPro\SearchableResource\Concerns\Appendable;
use BayAreaWebPro\SearchableResource\Concerns\Selectable;
use BayAreaWebPro\SearchableResource\Concerns\Orderable;
use BayAreaWebPro\SearchableResource\Concerns\Paginated;
use BayAreaWebPro\SearchableResource\Concerns\Sortable;
use BayAreaWebPro\SearchableResource\Concerns\Optional;
use BayAreaWebPro\SearchableResource\Concerns\Labeled;
use BayAreaWebPro\SearchableResource\Concerns\Withable;
use BayAreaWebPro\SearchableResource\Concerns\Whenable;

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
     * Options Formatter
     * @var FormatsOptions
     */
    protected FormatsOptions $formatter;

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
            } elseif (
                is_string($query)
                && class_exists($query)
                && is_subclass_of($query, AbstractQuery::class)) {
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
        if ($query instanceof ConditionalQuery) {
            $applies = $query->applies();
            $this->query->when($applies, $query);
            if($applies && $query instanceof ValidatableQuery){
                $this->withRules($query->getRules());
            }
        } else{
            $this->query->tap($query);
            if($query instanceof ValidatableQuery){
                $this->withRules($query->getRules());
            }
        }
        if($query instanceof ProvidesOptions){
            $this->withOptions($query->getOptions());
        }
        $this->fields([$query->getField()]);
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

            $options = Collection::make($this->options)
                ->map(fn($options, $key) => $this->formatOptions($key, Collection::make($options))->unique()->all())
                ->all();

            return Collection::make(array_merge([
                'order_by' => $this->formatOptions('order_by', $this->getOrderableOptions())->all(),
                'sort'     => $this->formatOptions('sort', $this->getSortOptions())->all(),
                'per_page' => $this->formatOptions('per_page', $this->getPerPageOptions())->all(),
            ], $options));
        }

        /**
         * Raw values arrays
         */
        return Collection::make(array_merge([
            'order_by' => $this->getOrderableOptions()->all(),
            'sort'     => $this->getSortOptions()->all(),
            'per_page' => $this->getPerPageOptions()->all(),
        ], $this->options));
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
     */
    public function toArray()
    {
        if (isset($this->paginate)) {
            $paginator = $this->executePaginatorQuery();
            return array_merge([
                'data' => $this->appendAppendable($paginator->items()),
            ], $this->getPaginatedAdditional($paginator), $this->with);
        }

        return array_merge([
            'data' => $this->appendAppendable($this->executeQuery()->all()),
        ], $this->getBaseAdditional(), $this->with);
    }

    /**
     * Format the paginated resource.
     * @return JsonResource
     */
    protected function formatPaginatedResource(): JsonResource
    {
        $paginator = $this->executePaginatorQuery();
        $items = $this->appendAppendable($paginator->items());
        return $this->resource::collection($items)->additional($this->getPaginatedAdditional($paginator));
    }

    /**
     * Format the base resource.
     * @return JsonResource
     */
    protected function formatBaseResource(): JsonResource
    {
        $items = $this->appendAppendable($this->executeQuery()->all());
        return $this->resource::collection($items)->additional($this->getBaseAdditional());
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
            'options'    => $this->getOptions(),
        ], $this->with);
    }

    /**
     * Get Additional Data for Base Queries
     * @return array
     */
    protected function getBaseAdditional(): array
    {
        return array_merge([
            'query'   => $this->formatQuery(),
            'options' => $this->getOptions()->forget('per_page'),
        ], $this->with);
    }
}
