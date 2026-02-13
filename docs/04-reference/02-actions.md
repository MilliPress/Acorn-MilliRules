---
title: 'Actions Reference'
post_excerpt: 'Full reference for all Acorn actions: redirect and set_header.'
menu_order: 90
---

# Actions Reference

This is the complete reference for all actions provided by the Acorn package. Actions modify the outgoing HTTP response through the [ResponseCollector](../03-customization/01-custom-conditions-and-actions.md#responsecollector-api).

## Redirect

Redirect the request to a different URL.

| Property                | Value                                                        |
|-------------------------|--------------------------------------------------------------|
| **Type**                | `redirect`                                                   |
| **Class**               | `MilliRules\Acorn\Packages\Acorn\Actions\Redirect` |
| **Arguments**           | `url` (string), `status` (int, default: `302`)               |
| **Placeholder support** | Yes — `{route.parameters.*}`, `{route.name}`, etc.           |

### Builder Syntax

```php
->redirect(string $url, int $status = 302)
```

### Behavior

- A redirect **replaces the entire original response**. The controller's response body is discarded.
- If multiple `redirect` actions fire, **the last one wins** — each call to `setRedirect()` overwrites the previous.
- Headers set by `setHeader` actions are applied to the redirect response as well.
- The redirect uses `Illuminate\Http\RedirectResponse` internally.

### Examples

Simple redirect with a permanent status:

```php
Rules::create('legacy-docs-redirect')
    ->when()
        ->routeName('docs.legacy')
    ->then()
        ->redirect('/docs', 301)
    ->register();
```

Temporary redirect (default 302):

```php
Rules::create('maintenance-redirect')
    ->when()
        ->routeName('docs.maintenance')
    ->then()
        ->redirect('/maintenance')
    ->register();
```

Dynamic redirect using placeholders:

```php
Rules::create('product-redirect')
    ->when()
        ->routeName('docs.old-product')
    ->then()
        ->redirect('/docs/{route.parameters.product}/latest', 301)
    ->register();
```

> [!WARNING]
> An empty URL (empty string) is silently ignored — no redirect occurs. Always ensure the URL argument resolves to a non-empty value.

### Array Syntax

```php
['type' => 'redirect', 'url' => '/docs', 'status' => 301]
['type' => 'redirect', 'url' => '/docs/{route.parameters.product}']
```

---

## Set Header

Add an HTTP response header.

| Property                | Value                                                         |
|-------------------------|---------------------------------------------------------------|
| **Type**                | `set_header`                                                  |
| **Class**               | `MilliRules\Acorn\Packages\Acorn\Actions\SetHeader` |
| **Arguments**           | `name` (string), `value` (string)                             |
| **Placeholder support** | Yes — `{route.parameters.*}`, `{route.name}`, etc.            |

### Builder Syntax

```php
->setHeader(string $name, string $value)
```

### Behavior

- Headers are **additive** — multiple `setHeader` calls with different header names all apply.
- For the **same header name**, the last value wins — `addHeader()` overwrites previous values for a given key.
- Headers are applied to both normal responses and redirect responses.
- An empty header name is silently ignored.

### Examples

Single header:

```php
Rules::create('nosniff-header')
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Content-Type-Options', 'nosniff')
    ->register();
```

Multiple headers in one rule:

```php
Rules::create('security-headers')
    ->when()
        ->routeName('docs.*', 'LIKE')
    ->then()
        ->setHeader('X-Content-Type-Options', 'nosniff')
        ->setHeader('X-Frame-Options', 'DENY')
        ->setHeader('X-XSS-Protection', '1; mode=block')
    ->register();
```

Dynamic value using placeholders:

```php
Rules::create('product-header')
    ->when()
        ->routeParameter('product')
    ->then()
        ->setHeader('X-Product', '{route.parameters.product}')
    ->register();
```

Cache control headers:

```php
Rules::create('cache-docs')
    ->when()
        ->routeName('docs.show')
    ->then()
        ->setHeader('Cache-Control', 'public, max-age=3600')
        ->setHeader('Vary', 'Accept-Encoding')
    ->register();
```

### Array Syntax

```php
['type' => 'set_header', 'name' => 'X-Custom', 'value' => 'hello']
['type' => 'set_header', 'name' => 'X-Product', 'value' => '{route.parameters.product}']
```

> [!TIP]
> Use `setHeader` for standard HTTP headers like `Cache-Control`, `Vary`, security headers, and custom `X-*` headers. The header name and value are passed directly to `Symfony\Component\HttpFoundation\ResponseHeaderBag::set()`.
