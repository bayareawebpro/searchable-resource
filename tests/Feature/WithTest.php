<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\TestCase;

class WithTest extends TestCase
{
    public function test_will_append_with()
    {
        $this->json('get', route('with', []))
            ->assertJson([
                "with"=> true,
            ])
        ;
    }
}
