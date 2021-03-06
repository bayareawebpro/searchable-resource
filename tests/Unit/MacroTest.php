<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\SearchableBuilder;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;
use Illuminate\Http\Request;

class MacroTest extends TestCase
{
    public function test_macroable()
    {
        SearchableBuilder::macro('getRequest', function(){
            return $this->request;
        });

        $builder = SearchableBuilder::make(MockUser::query());

        $this->assertInstanceOf(Request::class, $builder->getRequest());
    }
}
