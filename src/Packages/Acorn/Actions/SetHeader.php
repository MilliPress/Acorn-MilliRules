<?php

namespace MilliPress\AcornMilliRules\Packages\Acorn\Actions;

use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Set an HTTP response header.
 *
 * Builder usage:
 *   ->setHeader('X-Custom', 'value')
 *   ->setHeader('X-Product', '{route.parameters.product}')
 */
class SetHeader extends BaseAction
{
    public function get_type(): string
    {
        return 'set_header';
    }

    public function execute(Context $context): void
    {
        $name = $this->get_arg(0, '')->string();
        $value = $this->get_arg(1, '')->string();

        if ($name !== '') {
            app('millirules.response')->addHeader($name, $value);
        }
    }
}
