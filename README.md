# phalcon-graphql

GraphQL library for Phalcon, with schema definitions, field authorization,
model queries and mutations, and extensible plugins.

## Requirements

- PHP 8.4 or newer.
- Phalcon 5 or newer.
- `webonyx/graphql-php` 15.32.3 or newer within the 15.x line.
- `redound/phalcon-api` 7.x.

## Installation

```sh
composer require redound/phalcon-graphql:^9.0.1
```

## Releases and migration

See [CHANGELOG.md](CHANGELOG.md) for release notes, the
[migration guide for 9.0.1](CHANGELOG.md#migration-from-900), and the
[migration guide for 9.0.0](CHANGELOG.md#migration-from-8x).

## Tests

With the required PHP extensions and PDO SQLite installed, install the current
dependencies and run the isolated security regression suite:

```sh
composer update
composer test:security
composer audit --locked
```

The suite uses temporary SQLite databases. `composer.lock` is intentionally
ignored by this library; audit and test each application's own lockfile and
runtime when updating.
