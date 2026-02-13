---
title: 'Configuration'
post_excerpt: 'Configuration reference, middleware options, and stub customization.'
menu_order: 70
---

# Configuration

The configuration file controls how the MilliRules middleware is registered. Publish it with:

```bash
wp acorn vendor:publish --tag=millirules
```

This creates `config/millirules.php` in your application.

## Full Config Reference

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Control how the MilliRules middleware is registered. The middleware
    | executes rules after route matching and applies response modifications
    | (headers, redirects) to the outgoing HTTP response.
    |
    */

    'middleware' => [

        // Set to false to disable automatic middleware registration.
        'enabled' => true,

        // Middleware groups to attach to (e.g. ['web', 'api']).
        'groups' => ['web'],
    ],

];
```

### Options

| Key | Type | Default | Description |
|---|---|---|---|
| `middleware.enabled` | `bool` | `true` | Enable or disable automatic middleware registration. Set to `false` to register the middleware manually. |
| `middleware.groups` | `string[]` | `['web']` | Middleware groups the `ExecuteRules` middleware is pushed to. Rules will only execute on routes belonging to these groups. |

## Adding Middleware Groups

To execute rules on API routes as well:

```php
'middleware' => [
    'enabled' => true,
    'groups' => ['web', 'api'],
],
```

## Disabling Automatic Middleware

Set `middleware.enabled` to `false` to take full control over where the middleware runs:

```php
'middleware' => [
    'enabled' => false,
    'groups' => [],
],
```

Then register the middleware manually on specific routes or groups:

```php
use MilliPress\AcornMilliRules\Http\Middleware\ExecuteRules;

// On a specific route
Route::get('/docs/{product}', [DocsController::class, 'show'])
    ->middleware(ExecuteRules::class);

// On a route group
Route::middleware([ExecuteRules::class])->group(function () {
    Route::get('/docs/{product}', [DocsController::class, 'show']);
    Route::get('/docs/{product}/{path}', [DocsController::class, 'page']);
});
```

> [!TIP]
> Manual registration is useful when you only want rules to execute on a subset of routes, avoiding the overhead of rule evaluation on routes that never match any conditions.

## Customizing Stubs

After publishing, you can customize the stub templates used by the scaffolding commands. Published stubs are located at:

| Stub | Path |
|---|---|
| Rule | `stubs/millirules/rule.stub` |
| Action | `stubs/millirules/action.stub` |
| Condition | `stubs/millirules/condition.stub` |

Published stubs take priority over the package defaults. The scaffolding commands check for a published stub first and fall back to the package stub if none is found.

### Available Placeholders

| Placeholder | Replaced with |
|---|---|
| `{{ namespace }}` | The generated class namespace (e.g., `App\Rules`) |
| `{{ class }}` | The generated class name (e.g., `SecurityHeaders`) |
| `{{ ruleId }}` | Kebab-case rule ID, rule stubs only (e.g., `security-headers`) |
| `{{ type }}` | Snake_case type name, action and condition stubs only (e.g., `cors_headers`) |

### Example: Customized Rule Stub

```php
<?php

namespace {{ namespace }};

use MilliRules\Rules;

class {{ class }}
{
    public function register(): void
    {
        Rules::create('{{ ruleId }}')
            ->order(10)
            ->when()
                // Add conditions here
            ->then()
                // Add actions here
            ->register();
    }
}
```

## Next Steps

- **[Conditions Reference](../04-reference/01-conditions.md)** — full reference for all Acorn conditions
- **[Actions Reference](../04-reference/02-actions.md)** — full reference for all Acorn actions
- **[Route Context Reference](../04-reference/03-route-context.md)** — all available route context keys
