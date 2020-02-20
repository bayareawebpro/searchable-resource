<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\TestCase;

class OptionsTest extends TestCase
{
    public function test_will_append_options_from_rules()
    {
        $this->json('get', route('options', []))
            ->assertJson([
                'options' => [
                    'option' => ['my_option']
                ]
            ])
        ;
    }
}
