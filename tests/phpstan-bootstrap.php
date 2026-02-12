<?php

/**
 * PHPStan bootstrap stubs for Laravel/Acorn helper functions.
 *
 * These functions are provided at runtime by Acorn (roots/acorn)
 * but PHPStan cannot discover them without Larastan.
 */

if (! function_exists('app')) {
    /**
     * @template T
     *
     * @param  class-string<T>|string|null  $abstract
     * @return ($abstract is null ? \Illuminate\Contracts\Foundation\Application : ($abstract is class-string<T> ? T : mixed))
     */
    function app($abstract = null, array $parameters = [])
    {
    }
}

if (! function_exists('request')) {
    /**
     * @return \Illuminate\Http\Request
     */
    function request($key = null, $default = null)
    {
    }
}
