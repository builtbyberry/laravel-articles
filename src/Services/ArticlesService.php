<?php

namespace BuiltByBerry\LaravelArticles\Services;

use BuiltByBerry\LaravelArticles\Markdown\ChannelNotesParser;
use BuiltByBerry\LaravelArticles\Markdown\ChannelNotesStripper;
use BuiltByBerry\LaravelArticles\Markdown\FrontmatterParser;
use BuiltByBerry\LaravelArticles\Markdown\MarkdownRenderer;
use Illuminate\Support\Str;

/**
 * Renders git-native articles at `{content_path}/<slug>/article.md`.
 */
class ArticlesService
{
    private ?SeriesService $series = null;

    private ChannelNotesParser $channelNotesParser;

    public function __construct(
        private MarkdownRenderer $renderer,
        private FrontmatterParser $frontmatter,
        private ChannelNotesStripper $channelNotes,
        ?ChannelNotesParser $channelNotesParser = null,
    ) {
        $this->channelNotesParser = $channelNotesParser ?? new ChannelNotesParser;
    }

    public function setSeriesService(SeriesService $series): void
    {
        $this->series = $series;
    }

    public function contentPath(): string
    {
        return (string) config('articles.content_path', base_path('articles'));
    }

    public function urlPrefix(): string
    {
        return (string) config('articles.url_prefix', '/articles');
    }

    /**
     * @return array{html: string, toc: array<int, array{level: int, id: string, text: string}>, lastUpdated: ?string, readingMinutes: int, meta: array<string, mixed>}
     */
    public function render(string $slug): array
    {
        $path = $this->articlePath($slug);

        if (! file_exists($path)) {
            abort(404);
        }

        $raw = file_get_contents($path);
        [$meta, $body] = $this->frontmatter->parse($raw);
        $body = $this->channelNotes->strip($body);

        $markdown = $this->renderer->rewriteLinks($body, $this->urlPrefix());
        $html = $this->renderer->toHtml($markdown);
        $toc = $this->renderer->extractToc($html);
        $lastUpdated = $this->renderer->lastUpdated($path);
        $readingMinutes = $this->renderer->readingMinutes($body);

        return compact('html', 'toc', 'lastUpdated', 'readingMinutes', 'meta');
    }

    /**
     * @param  list<string>|null  $statuses
     * @return list<array{slug: string, title: string, kind: ?string, audience: ?string, description: ?string, ogImage: ?string, status: string, publishedAt: ?string, readingMinutes: ?int}>
     */
    public function discover(?array $statuses = null): array
    {
        $statuses ??= config('articles.discovery.index', ['ready', 'published']);
        $root = $this->contentPath();

        if (! is_dir($root)) {
            return [];
        }

        $cards = [];

        foreach (glob($root.'/*/article.md') ?: [] as $path) {
            $slug = basename(dirname($path));
            $raw = file_get_contents($path);
            [$meta, $rawBody] = $this->frontmatter->parse($raw);

            $status = (string) ($meta['status'] ?? 'draft');
            if (! in_array($status, $statuses, true)) {
                continue;
            }

            $body = $this->channelNotes->strip($rawBody);

            $card = [
                'slug' => $slug,
                'title' => (string) ($meta['title'] ?? Str::headline($slug)),
                'kind' => isset($meta['kind']) ? (string) $meta['kind'] : null,
                'audience' => isset($meta['audience']) ? (string) $meta['audience'] : null,
                'description' => isset($meta['description']) ? (string) $meta['description'] : null,
                'ogImage' => isset($meta['og_image']) ? (string) $meta['og_image'] : null,
                'status' => $status,
                'publishedAt' => isset($meta['published_at']) ? (string) $meta['published_at'] : null,
                'readingMinutes' => $this->renderer->readingMinutes($body),
                'mtime' => filemtime($path) ?: 0,
            ];

            $cards[] = $this->enrichCardWithSeries($card);
        }

        usort($cards, function (array $a, array $b): int {
            $aDate = $a['publishedAt'] ?? '';
            $bDate = $b['publishedAt'] ?? '';
            if ($aDate !== $bDate) {
                return strcmp($bDate, $aDate);
            }

            return $b['mtime'] <=> $a['mtime'];
        });

        return array_map(function (array $card): array {
            unset($card['mtime']);

            return $card;
        }, $cards);
    }

    /**
     * @param  list<string>|null  $statuses
     * @return array{slug: string, title: string, description: ?string, status: string, publishedAt: ?string, url: string, key: string, heading: string, markdown: string}|null
     */
    public function channelNote(string $slug, string $key, ?array $statuses = null): ?array
    {
        $path = $this->articlePath($slug);

        if (! file_exists($path)) {
            abort(404);
        }

        $raw = file_get_contents($path);

        if (! is_string($raw)) {
            abort(404);
        }

        [$meta, $body] = $this->frontmatter->parse($raw);
        $status = (string) ($meta['status'] ?? 'draft');
        $statuses ??= config('articles.discovery.index', ['ready', 'published']);

        if (! in_array($status, $statuses, true)) {
            abort(404);
        }

        $normalizedKey = Str::slug($key);
        $note = $this->channelNotesParser->extract($body)[$normalizedKey] ?? null;

        if ($note === null) {
            return null;
        }

        return [
            'slug' => $slug,
            'title' => (string) ($meta['title'] ?? Str::headline($slug)),
            'description' => isset($meta['description']) ? (string) $meta['description'] : null,
            'status' => $status,
            'publishedAt' => isset($meta['published_at']) ? (string) $meta['published_at'] : null,
            'url' => rtrim($this->urlPrefix(), '/').'/'.$slug,
            ...$note,
        ];
    }

    public function articlePath(string $slug): string
    {
        if (
            $slug === ''
            || $slug === '.'
            || $slug === '..'
            || str_contains($slug, '/')
            || str_contains($slug, '\\')
            || str_contains($slug, "\0")
        ) {
            abort(404);
        }

        return $this->contentPath()."/{$slug}/article.md";
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function enrichCardWithSeries(array $card): array
    {
        if ($this->series === null) {
            return $card;
        }

        $seriesSlug = $this->series->articleSeriesSlug($card['slug']);

        if ($seriesSlug === null) {
            return $card;
        }

        $manifest = collect($this->series->discoverManifests())->firstWhere('slug', $seriesSlug);

        if ($manifest === null) {
            return $card;
        }

        $part = array_search($card['slug'], $manifest['articles'], true);

        return array_merge($card, [
            'seriesSlug' => $seriesSlug,
            'seriesTitle' => $manifest['title'],
            'seriesPart' => $part !== false ? $part + 1 : null,
        ]);
    }
}
