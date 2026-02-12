<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rules:make:rule')]
class MakeRuleCommand extends GeneratorCommand
{
    protected $signature = 'rules:make:rule
                            {name : The rule class name (e.g. DocsPages)}
                            {--package=Acorn : Target package name}
                            {--force : Overwrite the rule if it already exists}';

    protected $aliases = ['rules:make'];

    protected $description = 'Scaffold a new rule class in app/Rules/';

    protected $type = 'Rule';

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
        $ruleId = Str::kebab($name);

        /** @var string $package */
        $package = $this->option('package');

        $this->components->bulletList([
            "Rule ID: <fg=blue>{$ruleId}</>",
            "Package: <fg=blue>{$package}</>",
            'Auto-discovered on next request',
        ]);

        return $result;
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/rule.stub');
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
        return $rootNamespace.'\Rules';
    }

    /**
     * @param  string  $name
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $className = class_basename($name);
        $ruleId = Str::kebab($className);

        return str_replace('{{ ruleId }}', $ruleId, $stub);
    }

    /**
     * @return array<string, array{string, string}>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => [
                'What should the rule be named?',
                'E.g. DocsPages',
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
