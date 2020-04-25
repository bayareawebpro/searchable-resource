<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\TestCase;

class OptionsTest extends TestCase
{
    public function test_will_append_options_from_queries()
    {
        $this->json('get', route('options', []))
            ->assertJson([
                'options' => [
                    'option' => ['my_option'],
                ],
            ]);
    }

    public function test_will_format_options_from_queries()
    {
        $this->json('get', route('options', ['formatted' => true]))
            ->assertJson([
                'options' => [
                    'option' => [
                        [
                            'label' => 'My Option',
                            'value' => 'my_option',
                        ],
                    ],
                ],
            ]);
    }
}
