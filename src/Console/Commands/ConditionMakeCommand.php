<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ConditionMakeCommand extends Command
{
    protected $signature = 'rules:make:condition
                            {name : The condition class name (e.g. IsAdmin)}';

    protected $description = 'Scaffold a new condition class in app/Rules/Conditions/';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $directory = $this->laravel->path('Rules/Conditions');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory."/{$name}.php";

        if (file_exists($filePath)) {
            $this->components->error("Condition class already exists: app/Rules/Conditions/{$name}.php");

            return self::FAILURE;
        }

        $type = Str::snake($name);
        $stub = $this->buildStub($name, $type);

        file_put_contents($filePath, $stub);

        $this->components->info("Condition created: app/Rules/Conditions/{$name}.php");
        $this->components->bulletList([
            "Condition type: <fg=blue>{$type}</>",
            "Builder: <fg=blue>->{$this->camelName($name)}(...)</>",
            'Auto-discovered via App\\Rules\\Conditions namespace',
        ]);

        return self::SUCCESS;
    }

    private function camelName(string $name): string
    {
        return Str::camel($name);
    }

    private function buildStub(string $name, string $type): string
    {
        return <<<PHP
<?php

namespace App\Rules\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

class {$name} extends BaseCondition
{
    public function get_type(): string
    {
        return '{$type}';
    }

    protected function get_actual_value(Context \$context)
    {
        // Return the value to compare. BaseCondition handles the operator + expected value.
        // \$context->get('route.name'), \$context->get('route.parameters.slug'), etc.
        return '';
    }
}

PHP;
    }
}
