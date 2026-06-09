<?php

namespace BuiltByBerry\LaravelArticles\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

class SeriesService
{
    /** @var array<string, string>|null */
    private ?array $articleToSeriesMap = null;

    public function __construct(
        private ArticlesService $articles,
    ) {}

    public function seriesPath(): string
    {
        $relative = trim((string) config('articles.series.path', '_series'), '/');

        return $this->articles->contentPath().'/'.$relative;
    }

    public function seriesUrlPrefix(): string
    {
        return (string) config('articles.series.url_prefix', '/articles/series');
    }

    /**
     * @return list<array{slug: string, title: string, description: string, articles: list<string>, index: array{featured: bool, order: int}}>
     */
    public function discoverManifests(): array
    {
        $root = $this->seriesPath();

        if (! is_dir($root)) {
            return [];
        }

        $manifests = [];

        foreach (glob($root.'/*.yaml') ?: [] as $path) {
            $slug = pathinfo($path, PATHINFO_FILENAME);
            $meta = $this->parseManifest(file_get_contents($path));

            $manifests[] = [
                'slug' => $slug,
                'title' => (string) ($meta['title'] ?? $slug),
                'description' => (string) ($meta['description'] ?? ''),
                'articles' => $this->normalizeSlugList($meta['articles'] ?? []),
                'index' => [
                    'featured' => (bool) ($meta['index']['featured'] ?? false),
                    'order' => (int) ($meta['index']['order'] ?? 100),
                ],
            ];
        }

        usort($manifests, fn (array $a, array $b): int => $a['index']['order'] <=> $b['index']['order']);

        return $manifests;
    }

    /**
     * @return list<array{slug: string, title: string, description: string, articles: list<array<string, mixed>>, indexOrder: int}>
     */
    public function discoverForIndex(?array $statuses = null): array
    {
        $bySlug = $this->articlesBySlug($statuses);

        return collect($this->discoverManifests())
            ->filter(fn (array $manifest): bool => $manifest['index']['featured'])
            ->map(function (array $manifest) use ($bySlug): array {
                $articles = $this->resolveSlugs($bySlug, $manifest['articles']);

                return [
                    'slug' => $manifest['slug'],
                    'title' => $manifest['title'],
                    'description' => $manifest['description'],
                    'articles' => $this->enrichWithSeriesPart($articles, $manifest['slug']),
                    'indexOrder' => $manifest['index']['order'],
                ];
            })
            ->filter(fn (array $section): bool => $section['articles'] !== [])
            ->sortBy('indexOrder')
            ->values()
            ->all();
    }

    /**
     * @return array{slug: string, title: string, description: string, articles: list<array<string, mixed>>}|null
     */
    public function resolveSeries(string $seriesSlug, ?array $statuses = null): ?array
    {
        $manifest = collect($this->discoverManifests())->firstWhere('slug', $seriesSlug);

        if ($manifest === null) {
            return null;
        }

        $bySlug = $this->articlesBySlug($statuses);
        $articles = $this->resolveSlugs($bySlug, $manifest['articles']);

        if ($articles === []) {
            return null;
        }

        return [
            'slug' => $manifest['slug'],
            'title' => $manifest['title'],
            'description' => $manifest['description'],
            'articles' => $this->enrichWithSeriesPart($articles, $manifest['slug']),
        ];
    }

    /**
     * @return array{slug: string, title: string, part: int, total: int, prev: ?array{slug: string, title: string}, next: ?array{slug: string, title: string}, siblings: list<array{slug: string, title: string, part: int, current: bool}>}|null
     */
    public function contextForArticle(string $articleSlug, ?array $statuses = null): ?array
    {
        $seriesSlug = $this->articleSeriesSlug($articleSlug);

        if ($seriesSlug === null) {
            return null;
        }

        $series = $this->resolveSeries($seriesSlug, $statuses);

        if ($series === null) {
            return null;
        }

        $articles = $series['articles'];
        $index = collect($articles)->search(fn (array $article): bool => $article['slug'] === $articleSlug);

        if ($index === false) {
            return null;
        }

        $part = $index + 1;
        $total = count($articles);
        $prev = $index > 0 ? $articles[$index - 1] : null;
        $next = $index < $total - 1 ? $articles[$index + 1] : null;

        $siblings = collect($articles)->map(fn (array $article, int $i): array => [
            'slug' => $article['slug'],
            'title' => $article['title'],
            'part' => $i + 1,
            'current' => $article['slug'] === $articleSlug,
        ])->all();

        return [
            'slug' => $series['slug'],
            'title' => $series['title'],
            'part' => $part,
            'total' => $total,
            'prev' => $prev ? ['slug' => $prev['slug'], 'title' => $prev['title']] : null,
            'next' => $next ? ['slug' => $next['slug'], 'title' => $next['title']] : null,
            'siblings' => $siblings,
        ];
    }

    public function articleSeriesSlug(string $articleSlug): ?string
    {
        return $this->buildArticleToSeriesMap()[$articleSlug] ?? null;
    }

    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function sitemapEntries(): array
    {
        $host = rtrim((string) config('articles.seo.canonical_host'), '/');
        $prefix = rtrim($this->seriesUrlPrefix(), '/');
        $entries = [];

        foreach ($this->discoverForIndex(config('articles.discovery.sitemap', ['published'])) as $section) {
            $entries[] = [
                'loc' => $host.$prefix.'/'.$section['slug'],
                'lastmod' => $this->latestLastmod($section['articles']),
                'changefreq' => (string) config('articles.series.sitemap_changefreq', 'monthly'),
                'priority' => (string) config('articles.series.sitemap_priority', '0.85'),
            ];
        }

        return $entries;
    }

    /**
     * @return array<string, string>
     */
    private function buildArticleToSeriesMap(): array
    {
        if ($this->articleToSeriesMap !== null) {
            return $this->articleToSeriesMap;
        }

        $map = [];

        foreach ($this->discoverManifests() as $manifest) {
            foreach ($manifest['articles'] as $articleSlug) {
                if (isset($map[$articleSlug])) {
                    if (app()->environment('local', 'testing')) {
                        Log::warning('Article belongs to multiple series; using first by index order.', [
                            'article' => $articleSlug,
                            'existing_series' => $map[$articleSlug],
                            'ignored_series' => $manifest['slug'],
                        ]);
                    }

                    continue;
                }

                $map[$articleSlug] = $manifest['slug'];
            }
        }

        return $this->articleToSeriesMap = $map;
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    private function articlesBySlug(?array $statuses = null): Collection
    {
        return collect($this->articles->discover($statuses))->keyBy('slug');
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $bySlug
     * @param  list<string>  $slugs
     * @return list<array<string, mixed>>
     */
    private function resolveSlugs(Collection $bySlug, array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug): ?array => $bySlug->get($slug))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $articles
     * @return list<array<string, mixed>>
     */
    private function enrichWithSeriesPart(array $articles, string $seriesSlug): array
    {
        $manifest = collect($this->discoverManifests())->firstWhere('slug', $seriesSlug);
        $title = $manifest['title'] ?? $seriesSlug;

        return collect($articles)
            ->values()
            ->map(fn (array $article, int $index): array => array_merge($article, [
                'seriesSlug' => $seriesSlug,
                'seriesTitle' => $title,
                'seriesPart' => $index + 1,
            ]))
            ->all();
    }

    /**
     * @return list<string>
     */
    private function normalizeSlugList(mixed $articles): array
    {
        if (! is_array($articles)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $articles)));
    }

    /**
     * @param  list<array<string, mixed>>  $articles
     */
    private function latestLastmod(array $articles): ?string
    {
        $latest = null;

        foreach ($articles as $article) {
            $path = $this->articles->articlePath($article['slug']);
            if (! file_exists($path)) {
                continue;
            }

            $mtime = filemtime($path);
            $candidate = $mtime ? date(DATE_ATOM, $mtime) : null;

            if ($candidate && ($latest === null || $candidate > $latest)) {
                $latest = $candidate;
            }
        }

        return $latest;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseManifest(string $raw): array
    {
        try {
            $meta = Yaml::parse($raw) ?? [];
        } catch (\Throwable) {
            return [];
        }

        return is_array($meta) ? $meta : [];
    }
}
