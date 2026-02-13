<?php

use Illuminate\Support\Facades\File;
use MilliRules\Acorn\Console\Commands\MakeActionCommand;
use MilliRules\Acorn\Console\Commands\MakeConditionCommand;
use MilliRules\Acorn\Console\Commands\MakeRuleCommand;

beforeEach(function () {
    // Register only the make commands (no full service provider).
    $this->app->register(new class ($this->app) extends \Illuminate\Support\ServiceProvider {
        public function register(): void
        {
        }

        public function boot(): void
        {
            $this->commands([
                MakeActionCommand::class,
                MakeConditionCommand::class,
                MakeRuleCommand::class,
            ]);
        }
    });
});

afterEach(function () {
    File::deleteDirectory($this->app->path('Rules'));
});

it('scaffolds an action class', function () {
    $this->artisan('rules:make:action', ['name' => 'CorsHeaders'])
        ->assertSuccessful();

    $path = $this->app->path('Rules/Actions/CorsHeaders.php');
    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('class CorsHeaders extends BaseAction');
    expect($content)->toContain("return 'cors_headers'");
    expect($content)->toContain('namespace App\Rules\Actions;');
});

it('scaffolds a condition class', function () {
    $this->artisan('rules:make:condition', ['name' => 'IsAdmin'])
        ->assertSuccessful();

    $path = $this->app->path('Rules/Conditions/IsAdmin.php');
    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('class IsAdmin extends BaseCondition');
    expect($content)->toContain("return 'is_admin'");
    expect($content)->toContain('namespace App\Rules\Conditions;');
});

it('scaffolds a rule class', function () {
    $this->artisan('rules:make:rule', ['name' => 'DocsPages'])
        ->assertSuccessful();

    $path = $this->app->path('Rules/DocsPages.php');
    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('class DocsPages');
    expect($content)->toContain("Rules::create('docs-pages')");
    expect($content)->toContain('namespace App\Rules;');
});

it('prevents overwriting without --force', function () {
    $this->artisan('rules:make:action', ['name' => 'CorsHeaders']);

    // GeneratorCommand returns false (exit code 1 via Symfony).
    $this->artisan('rules:make:action', ['name' => 'CorsHeaders'])
        ->expectsOutputToContain('already exists');
});

it('overwrites with --force', function () {
    $this->artisan('rules:make:action', ['name' => 'CorsHeaders']);

    $this->artisan('rules:make:action', ['name' => 'CorsHeaders', '--force' => true])
        ->assertSuccessful();
});

it('rejects reserved PHP names', function () {
    $this->artisan('rules:make:action', ['name' => 'Class'])
        ->expectsOutputToContain('reserved');
});
