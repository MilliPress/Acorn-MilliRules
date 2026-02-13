---
title: 'Conditions and Actions'
post_excerpt: 'Built-in Acorn conditions and actions with practical examples.'
menu_order: 40
---

# Conditions and Actions

Acorn MilliRules ships with three route-aware conditions and two HTTP response actions. These are registered automatically by the Acorn package and available in the fluent builder immediately.

## Conditions

### Route Name

Match the current Laravel route name. Useful for targeting named routes like `docs.show` or groups like `docs.*`.

```php
Rules::create('docs-headers')
    ->when()
        ->routeName('docs.show')
    ->then()
        ->setHeader('X-Docs', 'true')
    ->register();
```

Pattern matching with the `LIKE` operator:

```php
Rules::create('all-docs-headers')
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Section', 'docs')
    ->register();
```

For a complete list of operators and examples, see the [Route Name reference](../04-reference/01-conditions.md#route-name).

### Route Parameter

Check the value of a named route parameter. The first argument is the parameter name, the second is the expected value.

```php
Rules::create('millicache-product-header')
    ->when()
        ->routeParameter('product', 'millicache')
    ->then()
        ->setHeader('X-Product', 'millicache')
    ->register();
```

When only a parameter name is provided (no value), the condition checks if the parameter **exists**:

```php
Rules::create('has-product-parameter')
    ->when()
        ->routeParameter('product')
    ->then()
        ->setHeader('X-Has-Product', 'true')
    ->register();
```

For existence checks, pattern matching, and all operators, see the [Route Parameter reference](../04-reference/01-conditions.md#route-parameter).

### Route Controller

Match the fully-qualified controller class name handling the current route.

```php
Rules::create('docs-controller-headers')
    ->when()
        ->routeController('App\Http\Controllers\DocsController')
    ->then()
        ->setHeader('X-Handler', 'docs')
    ->register();
```

Partial matching with `LIKE`:

```php
Rules::create('any-docs-controller')
    ->when()
        ->routeController('*DocsController', 'LIKE')
    ->then()
        ->setHeader('X-Handler', 'docs')
    ->register();
```

For all operators and examples, see the [Route Controller reference](../04-reference/01-conditions.md#route-controller).

## Actions

### Set Header

Add an HTTP response header. The first argument is the header name, the second is the value.

```php
Rules::create('security-headers')
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Content-Type-Options', 'nosniff')
        ->setHeader('X-Frame-Options', 'DENY')
        ->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
    ->register();
```

Headers support placeholders that resolve from context:

```php
Rules::create('product-header')
    ->when()
        ->routeParameter('product')
    ->then()
        ->setHeader('X-Product', '{route.parameters.product}')
    ->register();
```

For full details on placeholder support and behavior, see the [Set Header reference](../04-reference/02-actions.md#set-header).

### Redirect

Redirect the request to a different URL. The first argument is the target URL, the second (optional) is the HTTP status code (default: `302`).

```php
Rules::create('legacy-docs-redirect')
    ->when()
        ->routeName('docs.legacy')
    ->then()
        ->redirect('/docs', 301)
    ->register();
```

Redirects support placeholders for dynamic URLs:

```php
Rules::create('product-redirect')
    ->when()
        ->routeName('docs.old-product')
    ->then()
        ->redirect('/docs/{route.parameters.product}/latest', 301)
    ->register();
```

For full details on redirect behavior and examples, see the [Redirect reference](../04-reference/02-actions.md#redirect).

## Combining Acorn and Core Conditions

You can mix Acorn route conditions with conditions from other MilliRules packages (like the PHP package's `request_url` or `cookie` conditions) in the same rule:

```php
Rules::create('docs-logged-in-redirect')
    ->when()
        ->routeName('docs.premium')
        ->cookie('session_token', '', '!=')
    ->then()
        ->setHeader('X-Access', 'premium')
    ->register();
```

By default, all conditions must match (`match_type: all`). To match when **any** condition is true, use `matchAny()`:

```php
Rules::create('product-pages')
    ->matchAny()
    ->when()
        ->routeName('products.show')
        ->routeParameter('product', 'millicache')
    ->then()
        ->setHeader('X-Product-Page', 'true')
    ->register();
```

> [!NOTE]
> For the full list of conditions and actions available from other packages (like `request_url`, `cookie`, `request_method`), see the [MilliRules Conditions Reference](https://github.com/MilliPress/MilliRules/blob/main/docs/05-reference/01-conditions.md) and [Actions Reference](https://github.com/MilliPress/MilliRules/blob/main/docs/05-reference/02-actions.md).

## Next Steps

- **[Artisan Commands](./03-artisan-commands.md)** — list, inspect, and scaffold rules from the CLI
- **[Custom Conditions and Actions](../03-customization/01-custom-conditions-and-actions.md)** — create your own condition and action types
- **[Conditions Reference](../04-reference/01-conditions.md)** — full reference for all Acorn conditions
- **[Actions Reference](../04-reference/02-actions.md)** — full reference for all Acorn actions
