---
title: 'Route Context Reference'
post_excerpt: 'All route context keys, types, placeholder usage, and empty context behavior.'
menu_order: 100
---

# Route Context Reference

The Acorn package provides a `route` context that exposes metadata about the currently matched Laravel route. This context is used by route conditions internally and can be accessed in custom conditions, custom actions, and placeholders.

## Context Keys

The route context is loaded via `$context->load('route')` and provides the following keys:

| Key                       | Type     | Description                               | Example                                                    |
|---------------------------|----------|-------------------------------------------|------------------------------------------------------------|
| `route.name`              | `string` | The named route identifier                | `'docs.show'`                                              |
| `route.parameters`        | `array`  | All route parameters as key-value pairs   | `['product' => 'millicache', 'path' => 'getting-started']` |
| `route.parameters.{name}` | `string` | A specific route parameter by name        | `'millicache'`                                             |
| `route.controller`        | `string` | The fully-qualified controller class name | `'App\Http\Controllers\DocsController'`                    |
| `route.action`            | `string` | The controller method name                | `'show'`                                                   |
| `route.uri`               | `string` | The route URI pattern (with placeholders) | `'/docs/{product}/{path?}'`                                |
| `route.middleware`        | `array`  | Middleware applied to the route           | `['web', 'auth']`                                          |

### Route Name

The route name as defined by `Route::name()` in your routes file. Returns an empty string if the route is unnamed.

```php
// Route definition
Route::get('/docs/{product}', [DocsController::class, 'show'])->name('docs.show');

// Context value
$context->get('route.name'); // 'docs.show'
```

### Route Parameters

All resolved route parameters as an associative array. Individual parameters can be accessed using dot notation:

```php
// Route definition
Route::get('/docs/{product}/{path?}', [DocsController::class, 'show']);

// Visiting: /docs/millicache/getting-started
$context->get('route.parameters');            // ['product' => 'millicache', 'path' => 'getting-started']
$context->get('route.parameters.product');    // 'millicache'
$context->get('route.parameters.path');       // 'getting-started'
```

### Controller and Action

The controller class name and method are extracted from the route's `uses` action:

```php
// Route definition: DocsController@show
$context->get('route.controller'); // 'App\Http\Controllers\DocsController'
$context->get('route.action');     // 'show'

// Invokable controller: DocsController (no @method)
$context->get('route.controller'); // 'App\Http\Controllers\DocsController'
$context->get('route.action');     // ''
```

### URI Pattern

The raw route URI pattern with parameter placeholders intact:

```php
$context->get('route.uri'); // '/docs/{product}/{path?}'
```

### Middleware

An array of middleware names or classes applied to the route:

```php
$context->get('route.middleware'); // ['web', 'auth']
```

## Using Context in Placeholders

Action arguments support `{context.key}` placeholders that are resolved at execution time. All route context keys are available:

```php
Rules::create('dynamic-redirect')
    ->when()
        ->routeParameter('product')
    ->then()
        ->redirect('/new-docs/{route.parameters.product}', 301)
    ->register();

Rules::create('dynamic-header')
    ->when()
        ->routeParameter('product')
    ->then()
        ->setHeader('X-Product', '{route.parameters.product}')
        ->setHeader('X-Route', '{route.name}')
    ->register();
```

## Using Context in Custom Code

In custom conditions and actions, load and access the route context through the `Context` object:

```php
use MilliRules\Context;

// In a custom condition's get_actual_value() or action's execute()
protected function get_actual_value(Context $context): string
{
    // Load route context (idempotent — safe to call multiple times)
    $context->load('route');

    // Read a value with a default
    $name = $context->get('route.name', '');

    return is_string($name) ? $name : '';
}
```

```php
use MilliRules\Context;

// In a custom action
public function execute(Context $context): void
{
    $context->load('route');

    $product = $context->get('route.parameters.product', '');

    if (is_string($product) && $product !== '') {
        app('millirules.response')->addHeader('X-Product', $product);
    }
}
```

> [!TIP]
> No need to call `$context->load('route')` before accessing route keys. You can use `$context->get('route')` directly. The load is lazy — route data is built only on the first call and cached for subsequent access.

## Empty Context Behavior

When there is no matched Laravel route (e.g., a 404 page or a request handled outside the router), the route context returns empty defaults:

| Key                       | Empty value                      |
|---------------------------|----------------------------------|
| `route.name`              | `''` (empty string)              |
| `route.parameters`        | `[]` (empty array)               |
| `route.parameters.{name}` | `''` (empty string, via default) |
| `route.controller`        | `''` (empty string)              |
| `route.action`            | `''` (empty string)              |
| `route.uri`               | `''` (empty string)              |
| `route.middleware`        | `[]` (empty array)               |

Route conditions will not match against empty values unless explicitly checking for empty strings. This means rules with route conditions naturally skip unmatched requests.
