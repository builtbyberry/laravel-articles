# Contributing

Thanks for your interest in improving `builtbyberry/laravel-articles`.

## Requirements

- PHP 8.5+
- Composer

## Getting started

```bash
git clone https://github.com/builtbyberry/laravel-articles.git
cd laravel-articles
composer install
```

## Running the checks

```bash
composer test   # Pest test suite (Orchestra Testbench)
composer lint   # Laravel Pint (code style)
```

Pint formats in place; run `vendor/bin/pint --test` to check without writing changes
(this is what CI runs).

## Pull requests

- Branch off `main` and open a PR against `main`.
- Keep changes focused; one logical change per PR.
- Add or update tests for any behavior change.
- Make sure `composer test` and `vendor/bin/pint --test` pass — CI runs both across
  Laravel 12 and 13 and must be green before merge.
- Update `CHANGELOG.md` under the `Unreleased` heading.

## Reporting bugs

Open an issue at
<https://github.com/builtbyberry/laravel-articles/issues> with a minimal
reproduction (the article frontmatter/markdown and config involved, plus the expected
vs. actual output).
