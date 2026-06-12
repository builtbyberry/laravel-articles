# Changelog

All notable changes to `builtbyberry/laravel-articles` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **BREAKING:** Raised the minimum PHP version to 8.5 (was 8.3).

### Added

- MIT `LICENSE` file.
- GitHub Actions CI running the test suite and Pint across Laravel 12 and 13.
- `CHANGELOG.md` and `CONTRIBUTING.md`.
- Unit tests for the markdown layer (`FrontmatterParser`, `MarkdownRenderer`).
- Expanded README with a configuration reference, custom-UI example, and OG image
  requirements.

### Removed

- The hardcoded `version` field in `composer.json`; versions are now derived from git
  tags.

## [0.2.0]

### Added

- Article series via YAML manifests in `{content_path}/_series/{slug}.yaml`, with
  ordered prev/next context, featured index sections, and series landing pages.
- `SeriesService` (`discoverForIndex()`, `resolveSeries()`, `contextForArticle()`,
  `sitemapEntries()`).

## [0.1.0]

### Added

- Initial release: git-native markdown articles engine with folder discovery,
  frontmatter status (`draft`/`ready`/`published`/`archived`), CommonMark rendering,
  Atom feed, sitemap, SEO metadata, and OG image generation command.

[Unreleased]: https://github.com/builtbyberry/laravel-articles/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/builtbyberry/laravel-articles/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/builtbyberry/laravel-articles/releases/tag/v0.1.0
