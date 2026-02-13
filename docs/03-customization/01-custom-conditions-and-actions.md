---
title: 'Custom Conditions and Actions'
post_excerpt: 'Create custom condition and action types, use the ResponseCollector, and access route context.'
menu_order: 60
---

# Custom Conditions and Actions

Beyond the built-in types, you can create your own conditions and actions that are auto-discovered and available in the fluent builder.

## Custom Conditions

### 1. Scaffold the Condition

```bash
wp acorn rules:make:condition IsAdmin
```

This creates `app/Rules/Conditions/IsAdmin.php`:

```php
<?php

namespace App\Rules\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

class IsAdmin extends BaseCondition
{
    public function get_type(): string
    {
        return 'is_admin';
    }

    protected function get_actual_value(Context $context)
    {
        // Return the value to compare. BaseCondition handles the operator + expected value.
        // $context->get('route.name'), $context->get('route.parameters.slug'), etc.
        return '';
    }
}
```

### 2. Implement the Logic

Fill in `get_actual_value()` to return the value that should be compared against the condition's expected value:

```php
<?php

namespace App\Rules\Conditions;

use MilliRules\Conditions\BaseCondition;
use MilliRules\Context;

class IsAdmin extends BaseCondition
{
    public function get_type(): string
    {
        return 'is_admin';
    }

    protected function get_actual_value(Context $context): string
    {
        $user = auth()->user();

        return $user && $user->is_admin ? 'true' : 'false';
    }
}
```

> [!NOTE]
> `get_actual_value()` should return a string (or scalar). The `BaseCondition` parent class handles all operator logic (`=`, `!=`, `LIKE`, `REGEXP`, `IN`) automatically.

### 3. Use in a Rule

The condition is auto-discovered and immediately available as `->isAdmin()` in the builder:

```php
Rules::create('admin-debug-headers')
    ->when()
        ->isAdmin('true')
    ->then()
        ->setHeader('X-Debug', 'enabled')
    ->register();
```

### 4. Verify

```bash
# Confirm the condition is registered
wp acorn rules:conditions --package=Acorn

# Confirm the rule uses it
wp acorn rules:show admin-debug-headers
```

## Custom Actions

### 1. Scaffold the Action

```bash
wp acorn rules:make:action CorsHeaders
```

This creates `app/Rules/Actions/CorsHeaders.php`:

```php
<?php

namespace App\Rules\Actions;

use MilliRules\Actions\BaseAction;
use MilliRules\Context;

class CorsHeaders extends BaseAction
{
    public function get_type(): string
    {
        return 'cors_headers';
    }

    public function execute(Context $context): void
    {
        // $value = $this->get_arg(0, 'default')->string();
        //
        // Modify the HTTP response:
        // app('millirules.response')->addHeader('X-Custom', $value);
        // app('millirules.response')->setRedirect('/path', 302);
    }
}
```

### 2. Implement the Logic

Use `$this->get_arg()` to read arguments from the builder and `app('millirules.response')` to modify the HTTP response:

```php
<?php

namespace App\Rules\Actions;

use MilliRules\Actions\BaseAction;
use MilliRules\Context;

class CorsHeaders extends BaseAction
{
    public function get_type(): string
    {
        return 'cors_headers';
    }

    public function execute(Context $context): void
    {
        $origin = $this->get_arg(0, '*')->string();

        $collector = app('millirules.response');
        $collector->addHeader('Access-Control-Allow-Origin', $origin);
        $collector->addHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $collector->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### 3. Use in a Rule

The action is auto-discovered and available as `->corsHeaders()` in the builder:

```php
Rules::create('api-cors')
    ->when()
        ->routeName('api.*', 'LIKE')
    ->then()
        ->corsHeaders('https://example.com')
    ->register();
```

### 4. Verify

```bash
# Confirm the action is registered
wp acorn rules:actions --package=Acorn

# Confirm the rule uses it
wp acorn rules:show api-cors
```

## ResponseCollector API

Custom actions modify the HTTP response through the `ResponseCollector` singleton, accessed via `app('millirules.response')`. The middleware reads the collector after rule execution and applies changes to the outgoing response.

### Available Methods

| Method | Description |
|---|---|
| `addHeader(string $name, string $value)` | Queue a header to be set on the response. If the same header name is added multiple times, the last value wins. |
| `setRedirect(string $url, int $status = 302)` | Queue a redirect response. Replaces the original response entirely. If multiple redirects are queued, the last one wins. |

```php
$collector = app('millirules.response');

// Add a header
$collector->addHeader('X-Custom', 'value');

// Queue a redirect (replaces the response)
$collector->setRedirect('/new-location', 301);
```

> [!WARNING]
> A redirect replaces the entire original response. Headers are still applied to the redirect response, but the original response body is discarded.

## Using Route Context in Custom Types

Custom conditions and actions can access the route context by loading it from the `Context` object:

```php
protected function get_actual_value(Context $context): string
{
    // Load route context (lazy-loaded on first call)
    $context->load('route');

    // Access individual keys
    $routeName = $context->get('route.name', '');
    $productParam = $context->get('route.parameters.product', '');
    $controller = $context->get('route.controller', '');

    return is_string($routeName) ? $routeName : '';
}
```

The `$context->load('route')` call is idempotent — it loads route data once and subsequent calls are no-ops. See the [Route Context reference](../04-reference/03-route-context.md) for all available context keys.

## Next Steps

- **[Configuration](./02-configuration.md)** — customize middleware groups and stubs
- **[Route Context Reference](../04-reference/03-route-context.md)** — all available context keys, types, and examples
- For advanced patterns like custom operators and context providers, see the [MilliRules Custom Conditions](https://github.com/MilliPress/MilliRules/blob/main/docs/03-customization/01-custom-conditions.md) and [Custom Actions](https://github.com/MilliPress/MilliRules/blob/main/docs/03-customization/02-custom-actions.md) documentation
