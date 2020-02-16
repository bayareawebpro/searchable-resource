<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

use Illuminate\Http\Request;

interface ValidatableQuery
{
    public function rules(?Request $request = null): array;
}
