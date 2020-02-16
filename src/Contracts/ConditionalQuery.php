<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

interface ConditionalQuery
{
    public function applies(Request $request): bool;
}
