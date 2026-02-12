<?php

namespace MilliPress\AcornMilliRules\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * We intentionally do NOT load the full AcornMilliRulesServiceProvider
     * because it calls MilliRules::init() which depends on Acorn runtime
     * infrastructure not available in a pure Testbench environment.
     *
     * Individual test files register only the components they need.
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }

    /**
     * Work around Acorn's HandleExceptions::flushState() calling
     * PHPUnit\Runner\ErrorHandler::enable() without the required
     * TestCase argument (incompatible with PHPUnit 12).
     */
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (\ArgumentCountError $e) {
            if (str_contains($e->getMessage(), 'ErrorHandler::enable()')) {
                return;
            }
            throw $e;
        }
    }
}
