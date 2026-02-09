<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RulesMakeCommand extends Command
{
    protected $signature = 'rules:make
                            {name : The rule class name (e.g. CacheDocsPages)}
                            {--package=Acorn : Target package name}';

    protected $description = 'Scaffold a new rule class in app/Rules/';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $package = $this->option('package');

        $directory = $this->laravel->path('Rules');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory."/{$name}.php";

        if (file_exists($filePath)) {
            $this->components->error("Rule class already exists: app/Rules/{$name}.php");

            return self::FAILURE;
        }

        $ruleId = Str::kebab($name);
        $stub = $this->buildStub($name, $ruleId, $package);

        file_put_contents($filePath, $stub);

        $this->components->info("Rule created: app/Rules/{$name}.php");
        $this->components->bulletList([
            "Rule ID: <fg=blue>{$ruleId}</>",
            "Package: <fg=blue>{$package}</>",
            'Auto-discovered on next request',
        ]);

        return self::SUCCESS;
    }

    /**
     * Build the rule class stub.
     */
    private function buildStub(string $name, string $ruleId, string $package): string
    {
        return <<<PHP
<?php

namespace App\Rules;

use MilliRules\Builders\ConditionBuilder;

class {$name}
{
    /**
     * Register this rule with MilliRules.
     *
     * Called automatically by the AcornMilliRulesServiceProvider.
     */
    public function register(): void
    {
        ConditionBuilder::create('{$ruleId}')
            ->packages(['{$package}'])
            // ->route_name('example.route')
            // ->request_url('/example/*', 'LIKE')
            ->build();
    }
}

PHP;
    }
}
