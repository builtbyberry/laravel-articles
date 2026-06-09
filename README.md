# builtbyberry/laravel-articles

Git-native markdown articles for Laravel.

## Install

```bash
composer require builtbyberry/laravel-articles
php artisan vendor:publish --tag=articles-config
```

## Content layout

```
articles/
  _template.md
  _series/
    my-series.yaml
  my-slug/
    article.md
```

Set `status` in frontmatter: `draft`, `ready`, `published`, or `archived`.

## Series (v0.2+)

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

Order comes from the `articles` list. Only slugs visible for the current surface are included; missing or draft slugs are skipped. One series per article — if a slug appears in multiple manifests, the first by `index.order` wins.

`SeriesService` exposes `discoverForIndex()`, `resolveSeries()`, `contextForArticle()` (prev/next), and `sitemapEntries()`.

## Routes

When `articles.routes.enabled` is true (default):

- `GET /articles` — index (`ready` + `published`)
- `GET /articles/series/{series}` — series landing page
- `GET /articles/{slug}` — show (all statuses reachable by URL)
- `GET /feed.xml` — Atom feed (`published` only)
- `GET /sitemap.xml` — sitemap (`published` + featured series landings)

Disable package routes and bind your own controllers to `ArticlesService` for Inertia or custom UIs.

## OG images

```bash
composer require --dev spatie/browsershot
php artisan articles:og-generate
```
