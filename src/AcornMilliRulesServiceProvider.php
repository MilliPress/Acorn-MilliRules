<?php

namespace MilliPress\AcornMilliRules;

use Illuminate\Support\ServiceProvider;
use MilliPress\AcornMilliRules\Console\Commands\ActionMakeCommand;
use MilliPress\AcornMilliRules\Console\Commands\ConditionMakeCommand;
use MilliPress\AcornMilliRules\Console\Commands\RuleMakeCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesActionsCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesConditionsCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesListCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesPackagesCommand;
use MilliPress\AcornMilliRules\Console\Commands\RulesShowCommand;
use MilliPress\AcornMilliRules\Http\Middleware\ExecuteRules;
use MilliPress\AcornMilliRules\Packages\Acorn\Package;
use MilliRules\MilliRules;
use MilliRules\Packages\PackageManager;
use MilliRules\Rules;

class AcornMilliRulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Package::class, function () {
            return new Package();
        });

        // Response collector: actions write here, middleware reads.
        $this->app->singleton('millirules.response', function () {
            return new ResponseCollector();
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
            RulesActionsCommand::class,
            RulesConditionsCommand::class,
            RuleMakeCommand::class,
            ActionMakeCommand::class,
            ConditionMakeCommand::class,
        ]);

        $this->discoverApplicationExtensions();
        $this->discoverApplicationRules();

        // Register the middleware that executes rules on web requests.
        $this->app['router']->pushMiddlewareToGroup('web', ExecuteRules::class);
    }

    /**
     * Register app-level action and condition namespaces for auto-discovery.
     */
    protected function discoverApplicationExtensions(): void
    {
        $rulesPath = $this->app->path('Rules');

        if (is_dir($rulesPath.'/Actions')) {
            Rules::register_namespace('Actions', 'App\\Rules\\Actions', 'Acorn');
        }

        if (is_dir($rulesPath.'/Conditions')) {
            Rules::register_namespace('Conditions', 'App\\Rules\\Conditions', 'Acorn');
        }
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

            if (is_object($instance) && method_exists($instance, 'register')) {
                $instance->register();
            }
        }
    }
}
