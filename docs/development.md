# Development

This page documents the tooling used to develop and maintain
`OpenErpByJsonRpc`: coding standards, static analysis, automated refactoring and
tests.

## Requirements

- PHP **8.5+**
- [Composer](https://getcomposer.org/)

## Installing the development dependencies

Most tools live in the main `require-dev` section, so a plain install is enough:

```bash
composer install
```

PHP CS Fixer is installed separately — see
[Coding standards](#coding-standards-php-cs-fixer) below.

## Tooling at a glance

| Tool | Purpose | Command |
| --- | --- | --- |
| [PHP CS Fixer](https://cs.symfony.com/) | Coding style (`@Symfony`) | `composer cs-fix` |
| [PHPStan](https://phpstan.org/) | Static analysis (level 8) | `vendor/bin/phpstan analyse` |
| [Rector](https://getrector.com/) | Automated refactoring | `vendor/bin/rector process` |
| [PHPUnit](https://phpunit.de/) | Test suite | `vendor/bin/phpunit` |

## Coding standards (PHP CS Fixer)

Code style follows the **`@Symfony`** rule set. The configuration lives in
[`.php-cs-fixer.dist.php`](../.php-cs-fixer.dist.php) and covers `src/` and
`tests/`.

PHP CS Fixer is **installed in an isolated dependency space** under
`tools/php-cs-fixer/`, not in the project's `require-dev`. This is required
because every 3.x release of PHP CS Fixer caps `sebastian/diff` at `^7.0`, while
PHPUnit 13 requires `sebastian/diff ^9.0`; the two cannot coexist in the same
`composer.json`. Isolation lets the project use a recent PHP CS Fixer release
that supports PHP 8.5.

Install it once:

```bash
composer install --working-dir=tools/php-cs-fixer
```

Then run it through the Composer scripts defined in the root `composer.json`:

```bash
# Report style violations without changing files
composer cs-check

# Fix style violations in place
composer cs-fix
```

Both scripts simply delegate to `tools/php-cs-fixer/vendor/bin/php-cs-fixer`.

## Static analysis (PHPStan)

The project is analysed at **level 8**, the strictest level. The configuration
lives in [`phpstan.neon`](../phpstan.neon) and covers `src/` and `tests/`.

```bash
vendor/bin/phpstan analyse
```

## Automated refactoring (Rector)

[Rector](https://getrector.com/) is configured in
[`rector.php`](../rector.php) with the `deadCode`, `codeQuality` and
`typeDeclarations` prepared sets, targeting PHP 8.5.

A project-specific rule,
`OpenErpByJsonRpc\Rector\SnakeCasePropertyToCamelCaseRector`
(in [`utils/rector/`](../utils/rector/)), renames `snake_case` identifiers
(properties, parameters and local variables) to `camelCase`, including every
`$this->` access.

```bash
# Preview the changes
vendor/bin/rector process --dry-run

# Apply them
vendor/bin/rector process
```

> Note: data keys exchanged with Odoo (array keys such as `'session_id'` or
> `'user_context'`) deliberately keep their original `snake_case` spelling —
> they are external contracts, not PHP identifiers.

## Tests (PHPUnit)

The test suite is written with PHPUnit 13:

```bash
vendor/bin/phpunit
```

The configuration in [`phpunit.xml`](../phpunit.xml) enables code coverage. If
Xdebug is not running in coverage mode, PHPUnit reports *"No tests executed!"*;
run the suite without coverage in that case:

```bash
vendor/bin/phpunit --no-coverage
```

Some tests need a running Odoo instance and a `tests/config.test.json` file;
those are skipped when the configuration is absent.

## Before committing

Run the full quality chain:

```bash
composer cs-fix
vendor/bin/phpstan analyse
vendor/bin/phpunit --no-coverage
```
