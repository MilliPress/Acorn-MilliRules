<?php

use MilliPress\AcornMilliRules\AcornMilliRulesServiceProvider;

it('merges default config when registered', function () {
    // Manually call only the config part of register() without the MilliRules init.
    $provider = new AcornMilliRulesServiceProvider($this->app);
    $provider->register();

    expect(config('millirules.middleware.enabled'))->toBeTrue();
    expect(config('millirules.middleware.groups'))->toBe(['web']);
});

it('has publishable assets', function () {
    $provider = new AcornMilliRulesServiceProvider($this->app);
    $provider->boot();

    $paths = AcornMilliRulesServiceProvider::pathsToPublish(
        AcornMilliRulesServiceProvider::class,
        'millirules'
    );

    expect($paths)->not->toBeEmpty();

    $joined = implode('|', array_values($paths));
    expect($joined)->toContain('millirules.php');
    expect($joined)->toContain('stubs/millirules');
});
