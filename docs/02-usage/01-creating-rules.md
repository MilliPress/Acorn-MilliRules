---
title: 'Creating Rules'
post_excerpt: 'Rule class anatomy, scaffolding, auto-discovery, ordering, and multiple rules per class.'
menu_order: 30
---

# Creating Rules

Rules in MilliRules are PHP classes with a `register()` method that uses the MilliRules fluent builder API to define conditions and actions.

## Rule Class Anatomy

A rule class lives in the `App\Rules` namespace and has a single `register()` method:

```php
<?php

namespace App\Rules;

use MilliRules\Rules;

class DocsRedirects
{
    public function register(): void
    {
        Rules::create('docs-redirect-old-paths')
            ->when()
                ->routeName('docs.legacy')
            ->then()
                ->redirect('/docs/{route.parameters.product}', 301)
            ->register();
    }
}
```

Key points:

- **Namespace**: `App\Rules` — auto-discovered by the service provider
- **No base class**: rule classes are plain PHP classes (no interface or abstract class required)
- **`register()` method**: called automatically during service provider boot
- **`Rules::create($id)`**: starts the fluent builder with a unique rule ID
- **`->register()`**: finalizes and registers the rule with the engine

> [!IMPORTANT]
> Each rule ID must be unique across all packages. Use descriptive IDs like `docs-security-headers` or `api-rate-limit-redirect`.

## Scaffolding

Use the `rules:make:rule` command to generate a new rule class:

```bash
wp acorn rules:make:rule DocsRedirects
```

Output:

```
Rule created successfully.
 ⇂ Rule ID: docs-redirects
 ⇂ Package: Acorn
 ⇂ Auto-discovered on next request
```

The rule ID is automatically derived from the class name using kebab-case conversion (`DocsRedirects` → `docs-redirects`).

### Options

| Option | Description |
|---|---|
| `--package=Acorn` | Target package name (default: `Acorn`) |
| `--force` | Overwrite the file if it already exists |

```bash
# Overwrite an existing rule
wp acorn rules:make:rule DocsRedirects --force
```

> [!TIP]
> `rules:make` is an alias for `rules:make:rule`. Both commands are identical.

## Multiple Rules in One Class

A single class can register multiple rules:

```php
<?php

namespace App\Rules;

use MilliRules\Rules;

class DocsRules
{
    public function register(): void
    {
        Rules::create('docs-security-headers')
            ->when()
                ->routeName('docs.*', 'LIKE')
            ->then()
                ->setHeader('X-Content-Type-Options', 'nosniff')
                ->setHeader('X-Frame-Options', 'DENY')
            ->register();

        Rules::create('docs-cache-headers')
            ->when()
                ->routeName('docs.show')
            ->then()
                ->setHeader('Cache-Control', 'public, max-age=3600')
            ->register();
    }
}
```

Each `Rules::create()` / `->register()` pair defines an independent rule with its own ID, conditions, and actions.

## Rule Ordering

Rules execute in order determined by their `order` value (default: `10`). Lower values execute first:

```php
Rules::create('early-rule')
    ->order(5)
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Processed-By', 'MilliRules')
    ->register();

Rules::create('late-rule')
    ->order(20)
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Cache', 'HIT')
    ->register();
```

## Disabling Rules

Temporarily disable a rule without removing it:

```php
Rules::create('maintenance-redirect')
    ->enabled(false)
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->redirect('/maintenance')
    ->register();
```

Disabled rules appear in `rules:list` with **Enabled = No** but are skipped during execution.

## Next Steps

- **[Conditions and Actions](./02-conditions-and-actions.md)** — explore the built-in Acorn conditions and actions with examples
- **[Custom Conditions and Actions](../03-customization/01-custom-conditions-and-actions.md)** — create your own condition and action types
- For details on the fluent builder API (`Rules::create()`, `->when()`, `->then()`, operators), see the [MilliRules Building Rules documentation](https://github.com/MilliPress/MilliRules/blob/main/docs/02-core-concepts/03-building-rules.md)
