<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Validation\Rule;

trait Validatable{

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
     * Compiled Validation Rules
     * @return array
     */
    public function rules(): array
    {
        return array_merge($this->rules, [
            'search'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'page'     => ['sometimes', 'numeric', 'min:1', 'max:' . PHP_INT_MAX],
            'sort'     => ['sometimes', 'string', Rule::in($this->getSortOptions()->all())],
            'order_by' => ['sometimes', 'string', Rule::in($this->getOrderableOptions()->all())],
            'per_page' => ['sometimes', 'numeric', Rule::in($this->getPerPageOptions()->all())],
        ]);
    }
}
