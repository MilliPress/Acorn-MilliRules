# Acorn MilliRules

[MilliRules](https://github.com/MilliPress/MilliRules) integration for [Acorn](https://roots.io/acorn/) — route-aware conditions, HTTP response actions, CLI commands, and automatic rule discovery for your WordPress sites powered by the [Roots.io Stack](https://roots.io/).

## Requirements

| Requirement   | Version               |
|---------------|-----------------------|
| PHP           | >= 8.1                |
| Roots Acorn   | ^4.0 \| ^5.0          |
| MilliRules    | ^0.7 (auto-installed) |

## Quick Start

```bash
composer require millipress/acorn-millirules
```

Scaffold your first rule:

```bash
wp acorn rules:make:rule RedirectLegacyPages
```

This creates `app/Rules/RedirectLegacyPages.php` with a ready-to-edit rule class that is auto-discovered by the service provider.

## Documentation

Full documentation is available at **[millipress.com/docs/acorn-millirules](https://millipress.com/docs/acorn-millirules/)** or in the [`docs/`](docs/) directory:

- [Introduction](docs/01-getting-started/01-introduction.md)
- [Installation](docs/01-getting-started/02-installation.md)
- [Creating Rules](docs/02-usage/01-creating-rules.md)
- [Conditions & Actions](docs/02-usage/02-conditions-and-actions.md)
- [Artisan Commands](docs/02-usage/03-artisan-commands.md)
- [Custom Conditions & Actions](docs/03-customization/01-custom-conditions-and-actions.md)
- [Configuration](docs/03-customization/02-configuration.md)
- [Reference: Conditions](docs/04-reference/01-conditions.md) · [Actions](docs/04-reference/02-actions.md) · [Route Context](docs/04-reference/03-route-context.md)

## Related

- **[MilliRules](https://github.com/MilliPress/MilliRules)** — the core rule engine (pure PHP, framework-agnostic)
- **[millipress.com](https://millipress.com)** — MilliPress documentation and resources for MilliRules and MilliCache

## License

GPL-2.0-or-later
