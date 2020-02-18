<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;

class LabelsTest extends TestCase
{
    public function test_will_append_labels()
    {
        factory(User::class, 10)->create();

        $this->json('get', route('labeled', []))
            ->assertJson([
            'options' =>[
                'per_page' => [
                    ["value" => 4, 'label' => "4 / Page"]
                ],
                'sort' => [
                    ["value" => 'asc', 'label' => "Asc"]
                ],
            ],
        ], true);
    }
}
