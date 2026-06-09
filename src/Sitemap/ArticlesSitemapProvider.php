<?php

namespace BuiltByBerry\LaravelArticles\Sitemap;

use BuiltByBerry\LaravelArticles\Services\ArticlesService;

class ArticlesSitemapProvider
{
    public function __construct(private ArticlesService $articles) {}

    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function entries(): array
    {
        $host = rtrim((string) config('articles.seo.canonical_host'), '/');
        $urlPrefix = (string) config('articles.url_prefix', '/articles');

        $entries = [
            [
                'loc' => $host.rtrim($urlPrefix, '/'),
                'lastmod' => null,
                'changefreq' => (string) config('articles.sitemap.index_changefreq', 'weekly'),
                'priority' => (string) config('articles.sitemap.index_priority', '0.9'),
            ],
        ];

        foreach ($this->articles->discover(config('articles.discovery.sitemap', ['published'])) as $article) {
            $path = $this->articles->articlePath($article['slug']);
            $lastmod = $this->lastmodFromPath($path) ?? $article['publishedAt'];

            $entries[] = [
                'loc' => $host.rtrim($urlPrefix, '/')."/{$article['slug']}",
                'lastmod' => $lastmod,
                'changefreq' => (string) config('articles.sitemap.article_changefreq', 'monthly'),
                'priority' => (string) config('articles.sitemap.article_priority', '0.8'),
            ];
        }

        return $entries;
    }

    private function lastmodFromPath(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $mtime = filemtime($path);

        return $mtime ? date(DATE_ATOM, $mtime) : null;
    }
}
