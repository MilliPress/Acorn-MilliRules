---
title: 'Introduction'
post_excerpt: 'What Acorn MilliRules provides and how it integrates the MilliRules engine with Acorn.'
menu_order: 10
---

# Introduction

Acorn MilliRules brings the [MilliRules Engine](https://github.com/MilliPress/MilliRules/blob/main/docs/01-getting-started/01-introduction.md) into your Acorn application. It adds route-aware conditions, HTTP response actions, CLI commands, and automatic rule discovery — everything you need to define and manage rules that react to Laravel routes.

## What This Package Provides

- **Route-aware conditions** — match rules by route name, route parameters, or controller class
- **HTTP response actions** — redirect requests or set response headers
- **Route context** — automatic context loading with route metadata (name, parameters, controller, URI, middleware)
- **8 Artisan commands** — list, inspect, and scaffold rules, actions, and conditions
- **Auto-discovery** — rule classes in `app/Rules/` are registered automatically
- **Middleware integration** — rules execute after route matching with zero configuration

> [!NOTE]
> This package extends MilliRules with Acorn-specific features. For general concepts like rules, conditions, actions, operators, and the fluent builder API, see the [MilliRules documentation](https://millipress.com/docs/millirules/).

## Prerequisites

| Requirement   | Version               |
|---------------|-----------------------|
| PHP           | >= 8.1                |
| Roots Acorn   | ^4.0 or ^5.0          |
| MilliRules    | ^0.7 (auto-installed) |

MilliRules is declared as a Composer dependency and will be installed automatically. You do not need to install it separately.

## How It Works

The execution flow in Acorn follows these steps:

1. **Service provider boots** — `AcornMilliRulesServiceProvider` initializes MilliRules, registers the Acorn package, and discovers your rule classes
2. **Auto-discovery** — rule classes in `app/Rules/*.php` are instantiated and their `register()` method is called, which registers rules with the engine
3. **Request arrives** — Laravel routes the request to a controller as usual
4. **Middleware executes** — the `ExecuteRules` middleware runs *after* the controller, so route context (name, parameters, controller) is available
5. **Rules evaluate** — MilliRules evaluates all registered rules against the current context
6. **Response modified** — actions collect response modifications (headers, redirects) via the `ResponseCollector`, and the middleware applies them to the outgoing HTTP response

> [!TIP]
> The builder API supports both **camelCase** (`->routeName()`, `->setHeader()`) and **snake_case** (`->route_name()`, `->set_header()`) method names. This documentation uses camelCase to align with Laravel conventions, but both styles work identically.

## Next Steps

- **[Installation](./02-installation.md)** — install the package, publish config and stubs, create your first rule
- **[Creating Rules](../02-usage/01-creating-rules.md)** — learn the rule class pattern and auto-discovery
- **[Artisan Commands](../02-usage/03-artisan-commands.md)** — explore all 8 CLI commands

---

**Ready to get started?** Continue to the [Installation guide](./02-installation.md).
