<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    protected $signature = 'rules:make:action
                            {name : The action class name (e.g. CorsHeaders)}
                            {--force : Overwrite the action if it already exists}';

    protected $description = 'Scaffold a new action class in app/Rules/Actions/';

    protected $type = 'Action';

    /**
     * @return bool|null
     */
    public function handle()
    {
        $result = parent::handle();

        if ($result === false) {
            return false;
        }

        $name = Str::studly(class_basename($this->getNameInput()));
        $type = Str::snake($name);

        $this->components->bulletList([
            "Action type: <fg=blue>{$type}</>",
            'Builder: <fg=blue>->'.Str::camel($name).'(...)</>',
            'Auto-discovered via App\\Rules\\Actions namespace',
        ]);

        return $result;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/action.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Rules\Actions';
    }

    /**
     * @param  string  $name
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $className = class_basename($name);
        $type = Str::snake($className);

        return str_replace('{{ type }}', $type, $stub);
    }

    /**
     * @return array<string, array{string, string}>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => [
                'What should the action be named?',
                'E.g. CorsHeaders',
            ],
        ];
    }

    /**
     * @return list<array<mixed>>
     */
    protected function getArguments(): array
    {
        return [];
    }
}
