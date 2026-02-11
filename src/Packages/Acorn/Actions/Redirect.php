<?php

namespace MilliPress\AcornMilliRules\Packages\Acorn\Actions;

use MilliRules\Actions\BaseAction;
use MilliRules\Context;

/**
 * Redirect to a different URL.
 *
 * Builder usage:
 *   ->redirect('/new-path', 301)
 *   ->redirect('/docs/{route.parameters.product}')
 */
class Redirect extends BaseAction
{
    public function get_type(): string
    {
        return 'redirect';
    }

    public function execute(Context $context): void
    {
        $url = $this->get_arg(0, '')->string();
        $status = $this->get_arg(1, 302)->int();

        if ($url !== '') {
            app('millirules.response')->setRedirect($url, $status);
        }
    }
}
