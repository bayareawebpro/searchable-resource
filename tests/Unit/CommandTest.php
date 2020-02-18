<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_command()
    {
        $this
            ->artisan('make:searchable NameQuery')
            ->assertExitCode(0);
        $this->assertFileExists(base_path('app/Http/Resources/Queries/NameQuery.php'));

        $this
            ->artisan('make:searchable RoleQuery')
            ->assertExitCode(0);
        $this->assertFileExists(base_path('app/Http/Resources/Queries/RoleQuery.php'));
    }

}
