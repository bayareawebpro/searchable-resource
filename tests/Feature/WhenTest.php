<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\TestCase;

class WhenTest extends TestCase
{
    public function test_will_append_with_when()
    {
        $this->json('get', route('when', ['when' => true]))
            ->assertJson([
                "with"=> true,
            ])
        ;
        $this->json('get', route('when', []))
            ->assertJsonMissing([
                "with"=> true,
            ], true)
        ;
    }
}
