<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\SearchableResourceBuilder;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\TestCase;
use Illuminate\Http\Request;

class MacroTest extends TestCase
{
    public function test_macroable()
    {
        SearchableResourceBuilder::macro('getRequest', function(){
            return $this->request;
        });

        $builder = SearchableResourceBuilder::make(User::query());

        $this->assertInstanceOf(Request::class, $builder->getRequest());
    }
}