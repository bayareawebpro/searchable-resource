<?php declare(strict_types=1);
namespace BayAreaWebPro\SearchableResource\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeQueryCommand extends GeneratorCommand{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'make:searchable {name : The required name of the query class}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create a Searchable Resource Query class';

    /**
     * The type of class being generated.
     * @var string
     */
    protected $type = 'Query';

    /**
     * Get the stub file for the generator.
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/Stubs/Query.stub';
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class.'],
        ];
    }

    /**
     * Get the default namespace for the class.
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace){
        return $rootNamespace.'\Http\Resources\Queries';
    }
}
