<?php

namespace MilliPress\AcornMilliRules\Packages\Acorn\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

/**
 * Route Parameter Condition
 *
 * Checks the value of a named route parameter.
 *
 * Name-based condition: first arg = parameter name, second arg = value.
 * When no value is specified, checks if the parameter EXISTS.
 *
 * Supported operators: =, !=, LIKE, REGEXP, IN, EXISTS, NOT EXISTS
 *
 * Array syntax:
 * - Check existence: ['type' => 'route_parameter', 'name' => 'product']
 * - Check value: ['type' => 'route_parameter', 'name' => 'product', 'value' => 'millicache']
 * - Pattern: ['type' => 'route_parameter', 'name' => 'product', 'value' => 'milli*', 'operator' => 'LIKE']
 *
 * Builder syntax:
 * - ->route_parameter('product')                  // EXISTS check
 * - ->route_parameter('product', 'millicache')    // exact match
 * - ->route_parameter('product', 'milli*', 'LIKE') // pattern match
 */
class RouteParameter extends BaseCondition
{
    /**
     * Define argument mapping for name-based condition.
     *
     * @return array<int, string>
     */
    public static function get_argument_mapping(): array
    {
        return ['name', 'value'];
    }

    /**
     * Get the condition type.
     */
    public function get_type(): string
    {
        return 'route_parameter';
    }

    /**
     * Check if the condition matches.
     *
     * Override for existence check when no value is specified.
     */
    public function matches(Context $context): bool
    {
        $check_value = array_key_exists('value', $this->config);

        if (! $check_value) {
            $actual = $this->get_actual_value($context);

            if ($this->operator === 'EXISTS' || $this->operator === '=' || $this->operator === 'IS') {
                return $actual !== '';
            }

            if ($this->operator === 'NOT EXISTS' || $this->operator === '!=' || $this->operator === 'IS NOT') {
                return $actual === '';
            }

            return $actual !== '';
        }

        return parent::matches($context);
    }

    /**
     * Get the actual value from context.
     *
     * @param Context $context The execution context.
     * @return string The parameter value or empty string.
     */
    protected function get_actual_value(Context $context): string
    {
        $context->load('route');

        $name = $this->config['name'] ?? '';

        if (! is_string($name) || $name === '') {
            return '';
        }

        $value = $context->get("route.parameters.{$name}", '');

        return is_scalar($value) ? (string) $value : '';
    }
}
