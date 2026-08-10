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

## Releases

The package follows Semantic Versioning. Composer package versions come from git
tags, so do not add a `version` field to `composer.json`.

To prepare a release:

1. Choose the next version from the public API changes in `Unreleased`.
2. Rename the populated `Unreleased` section to `[x.y.z] - YYYY-MM-DD` and add a
   new, empty `Unreleased` section above it.
3. Update the compare links at the bottom of `CHANGELOG.md`.
4. Run the full tests and formatting checks for every supported Laravel version.
5. Merge the release commit, create an annotated `vx.y.z` tag from that commit,
   push the tag, and publish matching GitHub release notes.

Packagist reads the git tag as the Composer version. The release commit, changelog
heading, tag, and GitHub release must all use the same version.

## Reporting bugs

Open an issue at
<https://github.com/builtbyberry/laravel-articles/issues> with a minimal
reproduction (the article frontmatter/markdown and config involved, plus the expected
vs. actual output).
