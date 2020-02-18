<?php declare(strict_types=1);

namespace BayAreaWebPro\SearchableResource\Tests\Unit;

use BayAreaWebPro\SearchableResource\Tests\TestCase;
use Illuminate\Support\Facades\File;

class CommandTest extends TestCase
{
    public function test_command()
    {
        File::delete(base_path('app/Http/Resources/Searchable/Queries/RoleQuery.php'));
        File::delete(base_path('app/Http/Resources/Searchable/Queries/NameQuery.php'));
        File::delete(base_path('app/Http/Resources/Searchable/UserSearchable.php'));

        $this
            ->artisan('make:searchable NameQuery')
            ->assertExitCode(0);
        $this->assertFileExists(base_path('app/Http/Resources/Searchable/Queries/NameQuery.php'));

        $this
            ->artisan('make:searchable RoleQuery')
            ->assertExitCode(0);
        $this->assertFileExists(base_path('app/Http/Resources/Searchable/Queries/RoleQuery.php'));

        $this
            ->artisan('make:searchable:builder UserSearchable')
            ->assertExitCode(0);
        $this->assertFileExists(base_path('app/Http/Resources/Searchable/UserSearchable.php'));
    }

}
