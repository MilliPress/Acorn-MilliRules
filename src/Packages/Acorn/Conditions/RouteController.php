<?php

namespace MilliRules\Acorn\Packages\Acorn\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

/**
 * Route Controller Condition
 *
 * Checks the current route's controller class name.
 *
 * Supported operators: =, !=, LIKE, REGEXP, IN
 *
 * Array syntax:
 * - Exact match: ['type' => 'route_controller', 'value' => 'App\Http\Controllers\DocsController']
 * - Pattern: ['type' => 'route_controller', 'value' => '*DocsController', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->route_controller('App\Http\Controllers\DocsController')
 * - ->route_controller('*DocsController', 'LIKE')
 */
class RouteController extends BaseCondition
{
    /**
     * Get the condition type.
     */
    public function get_type(): string
    {
        return 'route_controller';
    }

    /**
     * Get the actual value from context.
     *
     * @param  Context  $context  The execution context.
     * @return string The controller class name.
     */
    protected function get_actual_value(Context $context): string
    {
        $context->load('route');

        $controller = $context->get('route.controller', '');

        return is_string($controller) ? $controller : '';
    }
}
