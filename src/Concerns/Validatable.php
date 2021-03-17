<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait Validatable{

    /**
     * Add validation rules.
     * @param array $rules
     * @return $this
     */
    public function rules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    /**
     * Compiled Validation Rules
     * @return array
     */
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

    /**
     * Validate the request.
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateRequest(): void
    {
        $validator = $this->validator->make($this->request->all(), $this->compileRules());

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->messages());
        }

        $this->validated = $validator->validated();
    }

    /**
     * Get Validated Parameter
     * @param string $parameter
     * @param mixed $fallback
     * @return array|mixed
     */
    public function getParameter(string $parameter, $fallback = null)
    {
        return data_get($this->validated, $parameter,$fallback);
    }
}
