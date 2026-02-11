<?php

namespace MilliPress\AcornMilliRules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ActionMakeCommand extends Command
{
    protected $signature = 'rules:make:action
                            {name : The action class name (e.g. CorsHeaders)}';

    protected $description = 'Scaffold a new action class in app/Actions/';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $directory = $this->laravel->path('Actions');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory."/{$name}.php";

        if (file_exists($filePath)) {
            $this->components->error("Action class already exists: app/Actions/{$name}.php");

            return self::FAILURE;
        }

        $type = Str::snake($name);
        $stub = $this->buildStub($name, $type);

        file_put_contents($filePath, $stub);

        $this->components->info("Action created: app/Actions/{$name}.php");
        $this->components->bulletList([
            "Action type: <fg=blue>{$type}</>",
            "Builder: <fg=blue>->{$this->camelName($name)}(...)</>",
            'Auto-discovered via App\\Actions namespace',
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

namespace App\Actions;

use MilliRules\Actions\BaseAction;
use MilliRules\Context;

class {$name} extends BaseAction
{
    public function get_type(): string
    {
        return '{$type}';
    }

    public function execute(Context \$context): void
    {
        // \$value = \$this->get_arg(0, 'default')->string();
        //
        // Modify the HTTP response:
        // app('millirules.response')->addHeader('X-Custom', \$value);
        // app('millirules.response')->setRedirect('/path', 302);
    }
}

PHP;
    }
}
