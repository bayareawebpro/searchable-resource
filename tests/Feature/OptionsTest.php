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
                    'additional' => ['value']
                ],
            ], true);
    }

    public function test_will_format_options_from_queries()
    {
        $this->json('get', route('options', ['formatted' => true]))
            ->assertJson([
                'options' => [
                    'additional' => [
                        [
                            'label' => 'value',
                            'value' => 'value',
                        ],
                    ],
                    'untouched' => [
                        [
                            'label' => 'Value 1',
                            'value' => 'value1',
                        ],
                        [
                            'label' => 'Value 2',
                            'value' => 'value2',
                        ],
                    ],
                    'option' => [
                        [
                            'label' => 'my_option',
                            'value' => 'my_option',
                        ],
                    ],
                ],
            ], true);
    }
}
