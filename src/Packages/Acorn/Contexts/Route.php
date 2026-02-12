<?php

namespace MilliPress\AcornMilliRules\Packages\Acorn\Contexts;

use MilliRules\Contexts\BaseContext;

/**
 * Route Context
 *
 * Provides 'route' context with Laravel route metadata:
 * - name: Route name (e.g., 'docs.show')
 * - parameters: Route parameters as key-value pairs
 * - controller: Controller class name
 * - action: Controller method name
 * - uri: Route URI pattern (e.g., '/docs/{product}/{path?}')
 * - middleware: Array of middleware applied to the route
 */
class Route extends BaseContext
{
    /**
     * Get the context key.
     */
    public function get_key(): string
    {
        return 'route';
    }

    /**
     * Check if route context is available.
     *
     * Route data is only available when we have a matched Laravel route.
     */
    public function is_available(): bool
    {
        return function_exists('request')
            && request()->route() !== null; // @phpstan-ignore notIdentical.alwaysTrue
    }

    /**
     * Build the route context data.
     *
     * @return array<string, mixed>
     */
    protected function build(): array
    {
        $route = request()->route();

        if ($route === null) { // @phpstan-ignore identical.alwaysFalse
            return ['route' => $this->emptyRoute()];
        }

        $controller = '';
        $action = '';

        $uses = $route->getAction('uses');

        if (is_string($uses) && str_contains($uses, '@')) {
            [$controller, $action] = explode('@', $uses, 2);
        } elseif (is_string($uses)) {
            $controller = $uses;
        }

        return [
            'route' => [
                'name' => $route->getName() ?? '',
                'parameters' => $route->parameters() ?? [], // @phpstan-ignore nullCoalesce.expr
                'controller' => $controller,
                'action' => $action,
                'uri' => $route->uri(),
                'middleware' => $route->gatherMiddleware(),
            ],
        ];
    }

    /**
     * Return an empty route structure for consistency.
     *
     * @return array<string, mixed>
     */
    private function emptyRoute(): array
    {
        return [
            'name' => '',
            'parameters' => [],
            'controller' => '',
            'action' => '',
            'uri' => '',
            'middleware' => [],
        ];
    }
}
