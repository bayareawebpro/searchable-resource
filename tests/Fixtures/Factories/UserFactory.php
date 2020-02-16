<?php
/**
 * @var Factory $factory
 */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use BayAreaWebPro\SearchableResource\Tests\Fixtures\Models\User;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Str::random(15),
        'role' => $faker->randomElement(['admin', 'editor', 'guest']),
        'remember_token' => Str::random(10),
        'email_verified_at' => now(),
    ];
});
