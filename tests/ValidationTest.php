<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests;

use Illuminate\Support\Str;

class ValidationTest extends TestCase
{
    public function test_invalid_role()
    {
        $this->json('get', route('validation', [
            'role' => 'super-user' //Not In allowed values
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'role',
            ]);
    }

    public function test_invalid_search()
    {
        $this->json('get', route('validation', [
            'search' => Str::random(600),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'search',
            ]);
    }
}
