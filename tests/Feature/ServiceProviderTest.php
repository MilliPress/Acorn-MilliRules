<?php

use MilliRules\Acorn\ServiceProvider;

it('merges default config when registered', function () {
    // Manually call only the config part of register() without the MilliRules init.
    $provider = new ServiceProvider($this->app);
    $provider->register();

    expect(config('millirules.middleware.enabled'))->toBeTrue();
    expect(config('millirules.middleware.groups'))->toBe(['web']);
});

it('has publishable assets', function () {
    $provider = new ServiceProvider($this->app);
    $provider->boot();

    $paths = ServiceProvider::pathsToPublish(
        ServiceProvider::class,
        'millirules'
    );

    expect($paths)->not->toBeEmpty();

    $joined = implode('|', array_values($paths));
    expect($joined)->toContain('millirules.php');
    expect($joined)->toContain('stubs/millirules');
});
