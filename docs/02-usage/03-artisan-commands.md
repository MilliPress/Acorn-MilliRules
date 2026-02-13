---
title: 'Artisan Commands'
post_excerpt: 'All 8 CLI commands for listing, inspecting, and scaffolding rules.'
menu_order: 50
---

# Artisan Commands

Acorn MilliRules provides Artisan commands for managing rules from the CLI. All commands use the `rules:` prefix.

## Listing Commands

### `rules:list`

List all registered rules across loaded packages.

```bash
wp acorn rules:list
```

```
+--------------------+---------+-------+---------+-------+------------+---------+
| ID                 | Package | Order | Enabled | Match | Conditions | Actions |
+--------------------+---------+-------+---------+-------+------------+---------+
| security-headers   | Acorn   | 10    | Yes     | all   | 1          | 2       |
| docs-redirect      | Acorn   | 10    | Yes     | all   | 1          | 1       |
+--------------------+---------+-------+---------+-------+------------+---------+
```

#### Options

| Option | Description |
|---|---|
| `--package=<name>` | Filter by package name (e.g., `--package=Acorn`) |
| `--id=<pattern>` | Filter by rule ID substring (e.g., `--id=docs`) |

```bash
# Only Acorn rules
wp acorn rules:list --package=Acorn

# Rules containing "docs" in the ID
wp acorn rules:list --id=docs
```

### `rules:show`

Show detailed information about a specific rule.

```bash
wp acorn rules:show security-headers
```

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

#### Arguments

| Argument | Description |
|---|---|
| `id` | The rule ID to display (required) |

### `rules:packages`

List all registered MilliRules packages with their availability and rule counts.

```bash
wp acorn rules:packages
```

```
+-------+-----------+--------+--------------+-------+
| Name  | Available | Loaded | Dependencies | Rules |
+-------+-----------+--------+--------------+-------+
| PHP   | Yes       | Yes    | -            | 0     |
| Acorn | Yes       | Yes    | PHP          | 2     |
+-------+-----------+--------+--------------+-------+
```

### `rules:actions`

List all registered action types across loaded packages.

```bash
wp acorn rules:actions
```

```
+------------+------------------+---------+------------------------------------------------------+
| Type       | Builder          | Package | Class                                                |
+------------+------------------+---------+------------------------------------------------------+
| redirect   | ->redirect()     | Acorn   | MilliRules\Acorn\...\Actions\Redirect      |
| set_header | ->setHeader()    | Acorn   | MilliRules\Acorn\...\Actions\SetHeader     |
+------------+------------------+---------+------------------------------------------------------+
```

#### Options

| Option | Description |
|---|---|
| `--package=<name>` | Filter by package name |

```bash
wp acorn rules:actions --package=Acorn
```

### `rules:conditions`

List all registered condition types across loaded packages.

```bash
wp acorn rules:conditions
```

```
+------------------+----------------------+---------+------------------------------------------------------+
| Type             | Builder              | Package | Class                                                |
+------------------+----------------------+---------+------------------------------------------------------+
| route_name       | ->routeName()        | Acorn   | MilliRules\Acorn\...\Conditions\RouteName  |
| route_parameter  | ->routeParameter()   | Acorn   | MilliRules\Acorn\...\Conditions\Route...   |
| route_controller | ->routeController()  | Acorn   | MilliRules\Acorn\...\Conditions\Route...   |
+------------------+----------------------+---------+------------------------------------------------------+
```

#### Options

| Option | Description |
|---|---|
| `--package=<name>` | Filter by package name |

```bash
wp acorn rules:conditions --package=Acorn
```

## Scaffolding Commands

### `rules:make:rule`

Scaffold a new rule class in `app/Rules/`.

```bash
wp acorn rules:make:rule SecurityHeaders
```

```
Rule created successfully.
 ⇂ Rule ID: security-headers
 ⇂ Package: Acorn
 ⇂ Auto-discovered on next request
```

The class name is converted to a kebab-case rule ID: `SecurityHeaders` → `security-headers`.

#### Arguments and Options

| Argument / Option | Description |
|---|---|
| `name` | The rule class name (e.g., `SecurityHeaders`) |
| `--package=Acorn` | Target package name (default: `Acorn`) |
| `--force` | Overwrite the file if it already exists |

> [!TIP]
> `rules:make` is an alias for `rules:make:rule`.

### `rules:make:action`

Scaffold a new action class in `app/Rules/Actions/`.

```bash
wp acorn rules:make:action CorsHeaders
```

```
Action created successfully.
 ⇂ Action type: cors_headers
 ⇂ Builder: ->corsHeaders(...)
 ⇂ Auto-discovered via App\Rules\Actions namespace
```

The class name determines the action type (`CorsHeaders` → `cors_headers`) and builder method (`->corsHeaders()`).

#### Arguments and Options

| Argument / Option | Description |
|---|---|
| `name` | The action class name (e.g., `CorsHeaders`) |
| `--force` | Overwrite the file if it already exists |

### `rules:make:condition`

Scaffold a new condition class in `app/Rules/Conditions/`.

```bash
wp acorn rules:make:condition IsAdmin
```

```
Condition created successfully.
 ⇂ Condition type: is_admin
 ⇂ Builder: ->isAdmin(...)
 ⇂ Auto-discovered via App\Rules\Conditions namespace
```

The class name determines the condition type (`IsAdmin` → `is_admin`) and builder method (`->isAdmin()`).

#### Arguments and Options

| Argument / Option | Description |
|---|---|
| `name` | The condition class name (e.g., `IsAdmin`) |
| `--force` | Overwrite the file if it already exists |

## Customizing Stubs

All scaffolding commands use stub templates that you can customize after publishing:

```bash
wp acorn vendor:publish --tag=millirules
```

This copies the stubs to `stubs/millirules/` in your project root:

| Stub | Used by |
|---|---|
| `stubs/millirules/rule.stub` | `rules:make:rule` |
| `stubs/millirules/action.stub` | `rules:make:action` |
| `stubs/millirules/condition.stub` | `rules:make:condition` |

Published stubs take priority over the package defaults. Edit them to match your project's coding style or add boilerplate code.

## Next Steps

- **[Custom Conditions and Actions](../03-customization/01-custom-conditions-and-actions.md)** — create your own types using the scaffolding commands
- **[Configuration](../03-customization/02-configuration.md)** — customize middleware and published stubs
