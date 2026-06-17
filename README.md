# builtbyberry/laravel-articles

[![tests](https://github.com/builtbyberry/laravel-articles/actions/workflows/tests.yml/badge.svg)](https://github.com/builtbyberry/laravel-articles/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/builtbyberry/laravel-articles.svg)](https://packagist.org/packages/builtbyberry/laravel-articles)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Git-native markdown articles for Laravel. Drop markdown files into a folder, set a
`status` in frontmatter, and get an article index, individual pages, ordered series,
an Atom feed, and a sitemap — no database, no admin UI. Content lives in your repo and
ships with your deploys.

## Requirements

- PHP 8.5+ (the package targets 8.5 so it can use current PHP language features)
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

### Cross-article links

Link between articles with either a flat `slug.md` or the folder-style
`../other-slug/article.md` (which also resolves when the markdown is viewed on
GitHub). Both rewrite to `{url_prefix}/other-slug`; anchors (`#section`) are
preserved. Non-`.md` links are left untouched.

### Stripped sections

Headings listed in `strip_sections` (default `['Channel notes']`) are removed from
the **rendered page** — everything from `## <heading>` to the end of the document,
plus a preceding `---`. The source `article.md` is never modified, so the section
still appears in your editor, in git, and in GitHub's view. Matching is
heading-prefix based and whitespace-insensitive (`Channel notes` also strips
`## Channel notes (internal)`). Set `strip_sections` to `[]` to disable.

### Markdown and untrusted input

Articles are git-native and author-trusted by default, so the renderer allows raw
HTML and all link schemes. If you ever render untrusted markdown, set
`markdown.html_input` to `escape` (or `strip`) and `markdown.allow_unsafe_links` to
`false`.

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

Only the index, feed, and sitemap respect `status`. The show route renders **any**
status by slug, so a `draft` or `archived` article is reachable by anyone who knows
its URL — it's simply unlisted, not access-controlled. Gate it in your own
middleware if drafts must be private.

## Custom UI (Inertia, SPA, or your own Blade)

Disable the package routes and bind your own controllers to `ArticlesService` and
`SeriesService` — both are resolvable from the container (bound as `scoped`, so they
reset per request under Octane). If you wire them into your own long-lived singleton,
resolve them per request rather than caching the instance.

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
| `strip_sections` | `['Channel notes']` | Headings whose section is dropped from the rendered page (see below). |
| `markdown.html_input` | `allow` | CommonMark HTML handling: `allow`, `escape`, or `strip`. |
| `markdown.allow_unsafe_links` | `true` | When `false`, blocks `javascript:` and similar link schemes. |
| `last_updated.use_git` | `true` | Prefer git commit date for "last updated"; `false` uses file mtime only. |
| `last_updated.cache_ttl` | `86400` | Seconds to cache the resolved last-updated value. |
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

See [CHANGELOG.md](CHANGELOG.md) for release history and [UPGRADING.md](UPGRADING.md)
for version-to-version upgrade notes.

## License

MIT. See [LICENSE](LICENSE).

[browsershot]: https://github.com/spatie/browsershot
