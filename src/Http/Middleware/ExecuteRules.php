<?php

namespace MilliPress\AcornMilliRules\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MilliRules\MilliRules;
use Symfony\Component\HttpFoundation\Response;

/**
 * Execute MilliRules after route matching and apply response modifications.
 *
 * Runs after the controller so that route context (name, parameters, controller)
 * is available for condition evaluation. Response modifications collected by
 * the ResponseCollector (headers, redirects) are applied to the outgoing response.
 */
class ExecuteRules
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        MilliRules::execute_rules();

        $collector = app('millirules.response');

        // A redirect replaces the entire response.
        if ($collector->hasReplacement()) {
            $response = $collector->getReplacement();
        }

        // Apply queued headers (also applied to redirect responses).
        foreach ($collector->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }

        // Clear for next request (relevant in long-running processes).
        $collector->clear();

        return $response;
    }
}
