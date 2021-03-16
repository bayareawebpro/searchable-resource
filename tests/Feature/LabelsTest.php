<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Feature;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\MockUser;
use BayAreaWebPro\SearchableResource\Tests\TestCase;

class LabelsTest extends TestCase
{
    public function test_will_append_labels()
    {
        factory(MockUser::class, 10)->create();

        $this->json('get', route('labeled', []))
            ->assertJson([
            'options' =>[
                'per_page' => [
                    ["value" => 4, 'label' => "4 / Page"]
                ],
                'sort' => [
                    ["value" => 'asc', 'label' => "Asc"]
                ],
                'option' => [
                    ["value" => 'my_option', 'label' => "my_option"]
                ],
            ],
        ], true);
    }
}
