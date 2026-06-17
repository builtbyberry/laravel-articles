# Upgrading

Upgrade notes for `builtbyberry/laravel-articles`. Only releases that need consumer
action are listed.

## 0.2.0 → 0.3.0

### Requires PHP 8.5+ (breaking)

The minimum PHP version is now **8.5** (previously 8.3), so the package can adopt
current PHP language features. Upgrade your runtime to PHP 8.5 before running
`composer update`. On an unsupported runtime, Composer will refuse to resolve the
new version and leave you on 0.2.x.

### Service bindings are now `scoped` (behavioral)

`SeoMeta`, `SeriesService`, `ArticlesService`, `AtomFeedBuilder`, and
`ArticlesSitemapProvider` are now bound as `scoped` instead of `singleton`
(`MarkdownRenderer` is now an explicit `singleton`). This fixes per-request SEO
state and series caches bleeding across requests under Laravel Octane.

No action is required for typical apps — within a single request `scoped` behaves
exactly like `singleton`. **Only** if you resolve these services into your own
long-lived singleton under Octane, resolve them per request instead of caching the
instance, or they will hold stale state.

### New configuration (optional, no action required)

These keys default to the previous behavior; publish the config to customize them:

- `strip_sections` — headings removed from the rendered page (default
  `['Channel notes']`; set to `[]` to disable).
- `markdown.html_input` / `markdown.allow_unsafe_links` — harden rendering for
  untrusted input (permissive by default for trusted authors).
- `last_updated.use_git` / `last_updated.cache_ttl` — skip the git/`shell_exec`
  lookup on hosts without a git tree, and bound the cache entry lifetime.
