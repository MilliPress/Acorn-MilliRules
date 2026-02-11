<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RuleMakeCommand extends Command
{
    protected $signature = 'rules:make:rule
                            {name : The rule class name (e.g. DocsPages)}
                            {--package=Acorn : Target package name}';

    protected $aliases = ['rules:make'];

    protected $description = 'Scaffold a new rule class in app/Rules/';

    public function handle(): int
    {
        /** @var string $rawName */
        $rawName = $this->argument('name');
        $name = Str::studly($rawName);

        /** @var string $package */
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

    private function buildStub(string $name, string $ruleId, string $package): string
    {
        return <<<PHP
<?php

namespace App\Rules;

use MilliRules\Rules;

class {$name}
{
    /**
     * Register this rule with MilliRules.
     *
     * Called automatically by the AcornMilliRulesServiceProvider.
     */
    public function register(): void
    {
        Rules::create('{$ruleId}')
            ->when()
                // ->routeName('example.route')
                // ->requestUrl('/example/*', 'LIKE')
            ->then()
                // ->setHeader('X-Custom', 'value')
            ->register();
    }
}

PHP;
    }
}
