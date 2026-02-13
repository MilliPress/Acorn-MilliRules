<?php

namespace MilliRules\Acorn\Tests;

use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * We intentionally do NOT load the full ServiceProvider
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
     * Prevent PHPUnit 12's "removed error handlers" warning.
     *
     * Testbench's teardown calls HandleExceptions::flushHandlersState()
     * which strips ALL error handlers (including PHPUnit's) before
     * re-enabling them.  PHPUnit 12 detects that disruption and marks
     * the test as risky.
     *
     * We fix this by reversing what HandleExceptions::bootstrap() did
     * (one error handler + one exception handler) and then nulling the
     * static $app so flushState() short-circuits entirely.
     */
    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
        HandleExceptions::forgetApp();

        parent::tearDown();
    }
}
