<?php

namespace MilliPress\AcornMilliRules;

use Illuminate\Support\ServiceProvider;
use MilliPress\AcornMilliRules\Console\Commands\RulesListCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesMakeCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesPackagesCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesShowCommand;
use MilliPress\AcornMilliRules\Packages\Acorn\Package;
use MilliRules\MilliRules;
use MilliRules\Packages\PackageManager;

class AcornMilliRulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Package::class, function () {
            return new Package;
        });

        // Initialize MilliRules (registers PHP + WP packages) if not already done.
        if (! PackageManager::is_initialized()) {
            MilliRules::init();
        }

        // Register and load the Acorn package.
        $package = $this->app->make(Package::class);
        PackageManager::register_package($package);
        PackageManager::load_packages(['Acorn']);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->commands([
            RulesListCommand::class,
            RulesShowCommand::class,
            RulesPackagesCommand::class,
            RulesMakeCommand::class,
        ]);

        $this->discoverApplicationRules();
    }

    /**
     * Auto-discover and register rule classes in the host app's app/Rules/ directory.
     */
    protected function discoverApplicationRules(): void
    {
        $rulesPath = $this->app->path('Rules');

        if (! is_dir($rulesPath)) {
            return;
        }

        $files = glob($rulesPath.'/*.php');

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $className = 'App\\Rules\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($className)) {
                continue;
            }

            $instance = $this->app->make($className);

            if (method_exists($instance, 'register')) {
                $instance->register();
            }
        }
    }
}
