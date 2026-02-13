---
title: 'Conditions Reference'
post_excerpt: 'Full reference for all Acorn conditions: route_name, route_parameter, and route_controller.'
menu_order: 80
---

# Conditions Reference

This is the complete reference for all conditions provided by the Acorn package. Each condition checks a value from the [route context](./03-route-context.md).

## Route Name

Match the current Laravel route name.

| Property        | Value                                                            |
|-----------------|------------------------------------------------------------------|
| **Type**        | `route_name`                                                     |
| **Class**       | `MilliPress\AcornMilliRules\Packages\Acorn\Conditions\RouteName` |
| **Context key** | `route.name`                                                     |
| **Operators**   | `=`, `!=`, `LIKE`, `REGEXP`, `IN`                                |

### Builder Syntax

```php
->routeName(string $value, string $operator = '=')
```

### Examples

Exact match:

```php
Rules::create('docs-show-header')
    ->when()
        ->routeName('docs.show')
    ->then()
        ->setHeader('X-Page', 'docs-show')
    ->register();
```

Pattern match with `LIKE` (uses `*` as wildcard):

```php
Rules::create('all-docs-header')
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Section', 'docs')
    ->register();
```

Regular expression:

```php
Rules::create('docs-or-api-header')
    ->when()
        ->routeName('^(docs|api)\.', 'REGEXP')
    ->then()
        ->setHeader('X-App-Section', 'content')
    ->register();
```

Match one of several values with `IN`:

```php
Rules::create('special-pages-header')
    ->when()
        ->routeName(['docs.show', 'docs.index', 'blog.show'], 'IN')
    ->then()
        ->setHeader('X-Content', 'true')
    ->register();
```

### Array Syntax

```php
['type' => 'route_name', 'value' => 'docs.show']
['type' => 'route_name', 'value' => 'docs.*', 'operator' => 'LIKE']
```

---

## Route Parameter

Check the value of a named route parameter. This is a **name-based condition**: the first argument is the parameter name, the second is the expected value.

| Property             | Value                                                                 |
|----------------------|-----------------------------------------------------------------------|
| **Type**             | `route_parameter`                                                     |
| **Class**            | `MilliPress\AcornMilliRules\Packages\Acorn\Conditions\RouteParameter` |
| **Context key**      | `route.parameters.{name}`                                             |
| **Argument mapping** | `['name', 'value']`                                                   |
| **Operators**        | `=`, `!=`, `LIKE`, `REGEXP`, `IN`, `EXISTS`, `NOT EXISTS`             |

### Builder Syntax

```php
// Existence check (parameter exists and is not empty)
->routeParameter(string $name)

// Value check
->routeParameter(string $name, string $value, string $operator = '=')
```

### Existence Check

When only a parameter name is provided, the condition checks whether the parameter **exists** (is present and non-empty):

```php
Rules::create('has-product-param')
    ->when()
        ->routeParameter('product')
    ->then()
        ->setHeader('X-Has-Product', 'true')
    ->register();
```

Explicitly check that a parameter does **not** exist:

```php
Rules::create('no-product-param')
    ->when()
        ->routeParameter('product', '', 'NOT EXISTS')
    ->then()
        ->redirect('/products')
    ->register();
```

> [!NOTE]
> The existence check works by evaluating whether the parameter's value is a non-empty string. When no value argument is given, the default operator (`=`) behaves like `EXISTS` — it checks `actual !== ''`.

### Value Check

Compare the parameter value against an expected value:

```php
Rules::create('millicache-product')
    ->when()
        ->routeParameter('product', 'millicache')
    ->then()
        ->setHeader('X-Product', 'millicache')
    ->register();
```

Pattern match:

```php
Rules::create('milli-products')
    ->when()
        ->routeParameter('product', 'milli*', 'LIKE')
    ->then()
        ->setHeader('X-Product-Family', 'milli')
    ->register();
```

Regular expression:

```php
Rules::create('versioned-paths')
    ->when()
        ->routeParameter('path', '^v[0-9]+/', 'REGEXP')
    ->then()
        ->setHeader('X-Versioned', 'true')
    ->register();
```

### Array Syntax

```php
// Existence check
['type' => 'route_parameter', 'name' => 'product']

// Value check
['type' => 'route_parameter', 'name' => 'product', 'value' => 'millicache']

// Pattern match
['type' => 'route_parameter', 'name' => 'product', 'value' => 'milli*', 'operator' => 'LIKE']
```

---

## Route Controller

Match the fully qualified class name of the controller handling the current route.

| Property        | Value                                                                  |
|-----------------|------------------------------------------------------------------------|
| **Type**        | `route_controller`                                                     |
| **Class**       | `MilliPress\AcornMilliRules\Packages\Acorn\Conditions\RouteController` |
| **Context key** | `route.controller`                                                     |
| **Operators**   | `=`, `!=`, `LIKE`, `REGEXP`, `IN`                                      |

### Builder Syntax

```php
->routeController(string $value, string $operator = '=')
```

### Examples

Exact match with the full class name:

```php
Rules::create('docs-controller-header')
    ->when()
        ->routeController('App\Http\Controllers\DocsController')
    ->then()
        ->setHeader('X-Handler', 'docs')
    ->register();
```

Partial match with `LIKE`:

```php
Rules::create('any-api-controller')
    ->when()
        ->routeController('*Api*Controller', 'LIKE')
    ->then()
        ->setHeader('X-API', 'true')
    ->register();
```

Match one of several controllers with `IN`:

```php
Rules::create('content-controllers')
    ->when()
        ->routeController([
            'App\Http\Controllers\DocsController',
            'App\Http\Controllers\BlogController',
        ], 'IN')
    ->then()
        ->setHeader('X-Content', 'true')
    ->register();
```

### Array Syntax

```php
['type' => 'route_controller', 'value' => 'App\Http\Controllers\DocsController']
['type' => 'route_controller', 'value' => '*DocsController', 'operator' => 'LIKE']
```

> [!TIP]
> The controller value is the fully qualified class name as registered in the route (e.g., `App\Http\Controllers\DocsController`). For invokable controllers, the class name is returned without a method suffix.
