<?php

namespace MilliRules\Acorn\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rules:make:condition')]
class MakeConditionCommand extends GeneratorCommand
{
    protected $signature = 'rules:make:condition
                            {name : The condition class name (e.g. IsAdmin)}
                            {--force : Overwrite the condition if it already exists}';

    protected $description = 'Scaffold a new condition class in app/Rules/Conditions/';

    protected $type = 'Condition';

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
            "Condition type: <fg=blue>{$type}</>",
            'Builder: <fg=blue>->'.Str::camel($name).'(...)</>',
            'Auto-discovered via App\\Rules\\Conditions namespace',
        ]);

        return $result;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/condition.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        $stubName = basename($stub);

        if (file_exists($published = $this->laravel->basePath("stubs/millirules/{$stubName}"))) {
            return $published;
        }

        return dirname(__DIR__, 3).'/stubs/'.$stubName;
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Rules\Conditions';
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
                'What should the condition be named?',
                'E.g. IsAdmin',
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
