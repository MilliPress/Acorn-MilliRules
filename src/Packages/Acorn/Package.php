<?php

namespace MilliPress\AcornMilliRules\Packages\Acorn;

use MilliPress\AcornMilliRules\Packages\Acorn\Contexts\Route;
use MilliRules\Packages\BasePackage;

/**
 * Acorn Package
 *
 * Provides Acorn/Laravel route-aware conditions and context for MilliRules.
 * Available when the Acorn application container exists.
 */
class Package extends BasePackage
{
    /**
     * Get the unique package identifier.
     */
    public function get_name(): string
    {
        return 'Acorn';
    }

    /**
     * Get the namespaces provided by this package.
     *
     * @return array<int, string>
     */
    public function get_namespaces(): array
    {
        return [
            'MilliPress\\AcornMilliRules\\Packages\\Acorn\\Conditions',
            'MilliPress\\AcornMilliRules\\Packages\\Acorn\\Contexts',
        ];
    }

    /**
     * Check if this package is available in the current environment.
     *
     * Acorn package is available when the Acorn application container exists.
     */
    public function is_available(): bool
    {
        return function_exists('app');
    }

    /**
     * Get the names of packages required by this package.
     *
     * Requires PHP package. WP is optional — Acorn can run without WordPress rules loaded.
     *
     * @return array<int, string>
     */
    public function get_required_packages(): array
    {
        return ['PHP'];
    }

    /**
     * Discover context classes provided by this package.
     *
     * Overrides BasePackage's filesystem scanner because our context classes
     * live in a separate Composer package — BasePackage's __DIR__-relative
     * scanning would look in the wrong directory.
     *
     * @return array<int, string>
     */
    protected function discover_contexts(): array
    {
        return [
            Route::class,
        ];
    }
}
