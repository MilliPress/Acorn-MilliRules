<?php

namespace MilliRules\Acorn\Packages\Acorn\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

/**
 * Route Name Condition
 *
 * Checks the current Laravel route name.
 *
 * Supported operators: =, !=, LIKE, REGEXP, IN
 *
 * Array syntax:
 * - Exact match: ['type' => 'route_name', 'value' => 'docs.show']
 * - Pattern match: ['type' => 'route_name', 'value' => 'docs.*', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->route_name('docs.show')
 * - ->route_name('docs.*', 'LIKE')
 */
class RouteName extends BaseCondition
{
    /**
     * Get the condition type.
     */
    public function get_type(): string
    {
        return 'route_name';
    }

    /**
     * Get the actual value from context.
     *
     * @param  Context  $context  The execution context.
     * @return string The route name.
     */
    protected function get_actual_value(Context $context): string
    {
        $context->load('route');

        $name = $context->get('route.name', '');

        return is_string($name) ? $name : '';
    }
}
