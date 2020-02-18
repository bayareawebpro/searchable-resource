<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;


interface ValidatableQuery
{
    public function rules(): array;
}
