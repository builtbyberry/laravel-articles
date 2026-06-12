# builtbyberry/laravel-articles

[![tests](https://github.com/builtbyberry/laravel-articles/actions/workflows/tests.yml/badge.svg)](https://github.com/builtbyberry/laravel-articles/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/builtbyberry/laravel-articles.svg)](https://packagist.org/packages/builtbyberry/laravel-articles)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Git-native markdown articles for Laravel. Drop markdown files into a folder, set a
`status` in frontmatter, and get an article index, individual pages, ordered series,
an Atom feed, and a sitemap — no database, no admin UI. Content lives in your repo and
ships with your deploys.

## Requirements

- PHP 8.5+
- Laravel 12 or 13

## Install

```bash
composer require builtbyberry/laravel-articles
php artisan vendor:publish --tag=articles-config
```

The service provider is auto-discovered. Publishing the config is optional — the
package ships with working defaults — but recommended so you can set your content path,
SEO defaults, and routes.

## Content layout

```
articles/
  _template.md
  _series/
    my-series.yaml
  my-slug/
    article.md
```

Each article lives at `{content_path}/{slug}/article.md` with YAML frontmatter. Set
`status` in frontmatter: `draft`, `ready`, `published`, or `archived`. Status controls
where an article surfaces (see [Discovery statuses](#configuration)).

## Series

Define ordered article arcs in `{content_path}/_series/{slug}.yaml`:

```yaml
title: My series
description: |
  Optional markdown intro with [links](/articles/first-slug).
articles:
  - first-slug
  - second-slug
index:
  featured: true   # show as a section on the browse index
  order: 10        # section sort (lower first)
```

Order comes from the `articles` list. Only slugs visible for the current surface are
included; missing or draft slugs are skipped. One series per article — if a slug
appears in multiple manifests, the first by `index.order` wins.

`SeriesService` exposes `discoverForIndex()`, `resolveSeries()`, `contextForArticle()`
(prev/next), and `sitemapEntries()`.

## Routes

When `articles.routes.enabled` is true (default):

- `GET /articles` — index (`ready` + `published`)
- `GET /articles/series/{series}` — series landing page
- `GET /articles/{slug}` — show (all statuses reachable by URL)
- `GET /feed.xml` — Atom feed (`published` only)
- `GET /sitemap.xml` — sitemap (`published` + featured series landings)

## Custom UI (Inertia, SPA, or your own Blade)

Disable the package routes and bind your own controllers to `ArticlesService` and
`SeriesService` — both are registered as singletons in the container.

```php
// config/articles.php
'routes' => ['enabled' => false],
```

```php
use BuiltByBerry\LaravelArticles\Services\ArticlesService;

Route::get('/writing', function (ArticlesService $articles) {
    return Inertia::render('Articles/Index', [
        'articles' => $articles->discover(),
    ]);
});

Route::get('/writing/{slug}', function (string $slug, ArticlesService $articles) {
    return Inertia::render('Articles/Show', $articles->render($slug));
});
```

`discover()` returns article cards (slug, title, status, meta); `render($slug)` returns
`['html' => ..., 'meta' => ..., ...]` with the parsed frontmatter and rendered body.

## Configuration

All keys live in `config/articles.php` after publishing. The most useful ones:

| Key | Default | Purpose |
| --- | --- | --- |
| `content_path` | `base_path('articles')` | Root directory of article folders. |
| `url_prefix` | `/articles` | URL prefix for the index and article pages. |
| `route_names` | `articles`, `articles.show`, … | Named routes the package registers. |
| `routes.enabled` | `true` | Toggle the built-in routes off to bind your own. |
| `routes.middleware` | `['web']` | Middleware applied to package routes. |
| `discovery.index` | `['ready', 'published']` | Statuses shown on the index. |
| `discovery.feed` | `['published']` | Statuses included in the Atom feed. |
| `discovery.sitemap` | `['published']` | Statuses included in the sitemap. |
| `views.index` / `.show` / `.series` | `articles.*` | Blade views rendered for each surface. |
| `series.path` | `_series` | Subfolder holding series YAML manifests. |
| `series.url_prefix` | `/articles/series` | URL prefix for series landing pages. |
| `seo.canonical_host` | `env('APP_URL')` | Host used to build canonical/OG URLs. |
| `seo.site_name` | `env('APP_NAME')` | Site name for meta tags. |
| `seo.author` / `seo.publisher` | — | Author/publisher metadata for SEO + JSON-LD. |
| `seo.default_og_image` | `/images/og/site-default.png` | Fallback OG image. |
| `feed.*` | enabled, `/feed.xml`, title… | Atom feed path and metadata. |
| `sitemap.*` | enabled, `/sitemap.xml`, priorities… | Sitemap path, changefreq, priority. |
| `github_edit_base` | `null` | Optional base URL for "edit on GitHub" links. |
| `og.*` | view, output dirs, kind labels | OG image generation (see below). |

## OG images (optional)

Generating per-article Open Graph cards requires [`spatie/browsershot`][browsershot],
which is **not** installed by default. Browsershot drives a headless Chromium via
**Node + Puppeteer**, so the host running the command needs Node and a Chromium
install — this is the most common setup gotcha.

```bash
composer require --dev spatie/browsershot
# ensure Node + Puppeteer/Chromium are available on the host
php artisan articles:og-generate
```

Cards render from the `laravel-articles::og.article-card` view (override via
`og.view`) into `og.output_dir`.

## Testing

```bash
composer test   # Pest + Orchestra Testbench
composer lint   # Laravel Pint
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Bug reports and pull requests are welcome.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## License

MIT. See [LICENSE](LICENSE).

[browsershot]: https://github.com/spatie/browsershot
