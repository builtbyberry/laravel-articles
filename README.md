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
  my-slug/
    article.md
```

Set `status` in frontmatter: `draft`, `ready`, `published`, or `archived`.

## Routes

When `articles.routes.enabled` is true (default):

- `GET /articles` — index (`ready` + `published`)
- `GET /articles/{slug}` — show (all statuses reachable by URL)
- `GET /feed.xml` — Atom feed (`published` only)
- `GET /sitemap.xml` — sitemap (`published` only)

Disable package routes and bind your own controllers to `ArticlesService` for Inertia or custom UIs.

## OG images

```bash
composer require --dev spatie/browsershot
php artisan articles:og-generate
```
