<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait Validatable{

    public function rules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    public function compileRules(): array
    {
        return array_merge($this->rules, [
            'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'page'     => ['sometimes', 'numeric', 'min:1', 'max:' . PHP_INT_MAX],
            'sort'     => ['sometimes', 'string', Rule::in($this->getSortOptions()->all())],
            'order_by' => ['sometimes', 'string', Rule::in($this->getOrderableOptions()->all())],
            'per_page' => ['sometimes', 'numeric', Rule::in($this->getPerPageOptions()->all())],
        ]);
    }

    protected function validateRequest(): void
    {
        $this->params($this->request->validate($this->compileRules()));
    }

    public function getParameter(string $parameter, $fallback = null)
    {
        return data_get($this->parameters, $parameter,$fallback);
    }
}
