<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Contracts;

interface ConditionalQuery
{
    public function applies(): bool;
}
