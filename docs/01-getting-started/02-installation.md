---
title: 'Installation'
post_excerpt: 'Install, publish, verify, and create your first rule with Acorn MilliRules.'
menu_order: 20
---

# Installation

## Install the Package

```bash
composer require millipress/acorn-millirules
```

The service provider is registered automatically via the `extra.acorn.providers` key in the package's `composer.json` — no manual registration needed.

## Publish Config and Stubs

Publish the configuration file and stub templates:

```bash
wp acorn vendor:publish --tag=millirules
```

This publishes:

| File | Location |
|---|---|
| Configuration | `config/millirules.php` |
| Rule stub | `stubs/millirules/rule.stub` |
| Action stub | `stubs/millirules/action.stub` |
| Condition stub | `stubs/millirules/condition.stub` |

> [!TIP]
> Publishing is optional. The package works out of the box with sensible defaults. Publish only if you need to customize the middleware configuration or scaffold templates.

## Verify the Installation

Run these commands to confirm everything is registered:

```bash
# List registered packages (should show PHP, Acorn, and optionally WP)
wp acorn rules:packages

# List available action types
wp acorn rules:actions

# List available condition types
wp acorn rules:conditions
```

You should see the Acorn package listed with its `redirect`, `set_header` actions and `route_name`, `route_parameter`, `route_controller` conditions.

## Your First Rule

Let's create a rule that adds security headers to all documentation pages.

### 1. Scaffold the Rule

```bash
wp acorn rules:make:rule SecurityHeaders
```

This creates `app/Rules/SecurityHeaders.php`:

```php
<?php

namespace App\Rules;

use MilliRules\Rules;

class SecurityHeaders
{
    /**
     * Register this rule with MilliRules.
     *
     * Called automatically by the ServiceProvider.
     */
    public function register(): void
    {
        Rules::create('security-headers')
            ->when()
                // ->routeName('example.route')
                // ->requestUrl('/example/*', 'LIKE')
            ->then()
                // ->setHeader('X-Custom', 'value')
            ->register();
    }
}
```

### 2. Fill in the Rule

Replace the placeholders with real conditions and actions:

```php
<?php

namespace App\Rules;

use MilliRules\Rules;

class SecurityHeaders
{
    public function register(): void
    {
        Rules::create('security-headers')
            ->when()
                ->routeName('docs.*', 'LIKE')
            ->then()
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setHeader('X-Frame-Options', 'DENY')
            ->register();
    }
}
```

This rule matches any route whose name starts with `docs.` and adds two security headers to the response.

### 3. Verify the Rule

```bash
# List all rules — you should see security-headers
wp acorn rules:list

# Show details for this specific rule
wp acorn rules:show security-headers
```

The `rules:show` command displays the rule's conditions, actions, and metadata:

```
Rule ID ................................... security-headers
Package ............................................. Acorn
Order .................................................. 10
Enabled ............................................... Yes
Match Type ............................................ all

Conditions (1)
  route_name LIKE docs.*

Actions (2)
  set_header {"name":"X-Content-Type-Options","value":"nosniff"}
  set_header {"name":"X-Frame-Options","value":"DENY"}
```

> [!IMPORTANT]
> Rules are auto-discovered from `app/Rules/*.php`. There is no registration step beyond creating the file — the service provider finds and calls `register()` automatically.

## Next Steps

- **[Creating Rules](../02-usage/01-creating-rules.md)** — learn the full rule class pattern, ordering, and multiple rules per class
- **[Conditions and Actions](../02-usage/02-conditions-and-actions.md)** — explore all built-in Acorn conditions and actions
- **[Configuration](../03-customization/02-configuration.md)** — customize middleware groups and stubs
