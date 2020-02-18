<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Queries\UserQuery;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Resources\MockResource;
use BayAreaWebPro\SearchableResource\SearchableResource;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use BayAreaWebPro\SearchableResource\Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Validation\ValidationException;

class OptionsTest extends TestCase
{
    public function test_will_append_options_from_rules()
    {
        $this->json('get', route('options', []))
            ->assertJson([
                'options' => [
                    'key' => 'value'
                ]
            ])
        ;
    }
}
