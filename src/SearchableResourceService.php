<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Pagination\Paginator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

use BayAreaWebPro\SearchableResource\Contracts\ConditionalQuery;
use BayAreaWebPro\SearchableResource\Contracts\ValidatableQuery;
use BayAreaWebPro\SearchableResource\Contracts\InvokableQuery;

use BayAreaWebPro\SearchableResource\Concerns\Resourceful;
use BayAreaWebPro\SearchableResource\Concerns\Appendable;
use BayAreaWebPro\SearchableResource\Concerns\Orderable;
use BayAreaWebPro\SearchableResource\Concerns\Paginated;
use BayAreaWebPro\SearchableResource\Concerns\Sortable;

class SearchableResourceService implements Responsable
{
    use Sortable;
    use Orderable;
    use Paginated;
    use Appendable;
    use Resourceful;

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
     * Appendable Attributes
     * @var array
     */
    protected array $appendable = [];

    /**
     * Allowed Orderable Attributes
     * @var string
     */
    protected string $resource = JsonResource::class;

    /**
     * Invokable Queries
     * @var array
     */
    protected array $queries = [];

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
        return app(static::class, compact('query'));
    }

    /**
     * Compiled Validation Rules
     * @return array
     */
    public function rules(): array
    {
        return array_merge($this->rules, [
            'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort'     => ['sometimes', 'string', Rule::in($this->getSortOptions()->all())],
            'order_by' => ['sometimes', 'string', Rule::in($this->getOrderableOptions()->all())],
            'per_page' => ['sometimes', 'numeric', Rule::in($this->getPerPageOptions()->all())],
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
            if($query instanceof AbstractQuery){
                $this->query($query);
            }elseif(class_exists($query) && is_subclass_of($query, AbstractQuery::class)){
                $this->query($query::make($this->request));
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

        if($query instanceof ConditionalQuery){
            $this->query->when($query->applies($this->request), $query);
        }elseif($query instanceof InvokableQuery){
            $this->query->tap($query);
        }
        if($query instanceof ValidatableQuery) {
            foreach ($query->rules($this->request) as $rule) {
                $this->withRules($rule);
            }
        }
        return $this;
    }

    /**
     * Append request field to response query state.
     * @param array $fields
     * @return $this
     */
    public function withFields(array $fields): self
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * Add validation rules.
     * @param array $rules
     * @return $this
     */
    public function withRules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
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
        return $this->query->paginate($this->getPerPage());
    }

    /**
     * Execute queries and get the paginator instance.
     * @return EloquentCollection
     */
    protected function executeQuery(): EloquentCollection
    {
        $this->request->validate($this->rules());
        $this->query->orderBy($this->getOrderBy(), $this->getSort());
        return $this->query->get();
    }

    /**
     * Get the response representation of the data.
     * @param Request $request
     * @return Response
     */
    public function toResponse($request = null): Response
    {
        $this->request = $request ?: $this->request;

        if(isset($this->paginate)){
            $paginator = $this->executePaginatorQuery();
            $items = $this->appendAppendable($paginator->items());

            return $this->resource::collection($items)->additional([
                    'pagination' => $this->formatPaginator($paginator),
                    'query'      => $this->formatQuery($paginator),
                    'options'    => $this->getOptions(),
                ])->toResponse($request);
        }

        $items = $this->appendAppendable($this->executeQuery()->all());

        return $this->resource::collection($items)->additional([
            'query'      => $this->formatQuery(),
            'options'    => $this->getOptions()->forget('per_page'),
        ])->toResponse($request);
    }


    /**
     * Get the options for queries.
     * @return Collection
     */
    protected function getOptions(): Collection
    {
        /**
         * Airlock / Session Requests will receive formatted label / value pairs.
         */
        if($this->request->hasSession()){
            return Collection::make([
                'orderable' => $this->formatOrderableOptions()->pluck('value'),
                'per_page'  => $this->formatPerPageOptions()->pluck('value'),
                'sort'      => $this->formatSortOptions()->pluck('value'),
            ]);
        }

        /**
         * API Requests only get the values.
         */
        return Collection::make([
            'orderable' => $this->getOrderableOptions(),
            'per_page'  => $this->getPerPageOptions(),
            'sort'      => $this->getSortOptions(),
        ]);
    }
}
