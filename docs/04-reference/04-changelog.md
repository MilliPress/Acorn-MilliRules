---
title: 'Changelog'
post_excerpt: 'Version-by-version breakdown of new features, bug fixes, refactoring, and API changes in Acorn MilliRules.'
menu_order: 40
---

# Changelog

## 1.0.0 (2026-02-13)


### ⚠ BREAKING CHANGES

* The PHP namespace changed from MilliPress\AcornMilliRules to MilliRules\Acorn, and AcornMilliRulesServiceProvider was renamed to ServiceProvider. The Composer package name (millipress/acorn-millirules) remains unchanged.

### Features

* Add execution layer with ResponseCollector, built-in actions, and make commands ([e750afb](https://github.com/MilliPress/Acorn-MilliRules/commit/e750afbd3b2a752c2b69353fd6f52de4ab95bf13))
* Add rules:actions and rules:conditions CLI commands ([cc8e0a5](https://github.com/MilliPress/Acorn-MilliRules/commit/cc8e0a5288daa506c4b8a422ccf36d735d4337db))


### Bug Fixes

* **cli:** Deduplicate cross-package rules in rules:list output ([58987a6](https://github.com/MilliPress/Acorn-MilliRules/commit/58987a6a4f054d705f471dc5bba5899ce6d89b15))
* **cli:** Preserve wildcard types in builder column display ([485adc4](https://github.com/MilliPress/Acorn-MilliRules/commit/485adc4c609a015d5b92c88ce0e29b948a4199c8))
* Use project's Composer ClassLoader instead of first registered ([d00ea00](https://github.com/MilliPress/Acorn-MilliRules/commit/d00ea0083b97384e75a5785b13378c3d54e34686))


### Refactoring

* Move app action/condition namespaces under App\Rules ([f48d418](https://github.com/MilliPress/Acorn-MilliRules/commit/f48d418f2aeddb272850987d313b3271845765c8))
* Rename namespace to MilliRules\Acorn and class to ServiceProvider ([2d09b6f](https://github.com/MilliPress/Acorn-MilliRules/commit/2d09b6fb4c9d8b93b8b8aceac40d311c277fb632))
* Simplify type discovery using MilliRules 0.7.1 getters ([9cab3ae](https://github.com/MilliPress/Acorn-MilliRules/commit/9cab3aec01237187af5a928e4a772ae0a587360d))
