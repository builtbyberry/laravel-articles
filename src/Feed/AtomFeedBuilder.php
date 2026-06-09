<?php

namespace BuiltByBerry\LaravelArticles\Feed;

use BuiltByBerry\LaravelArticles\Services\ArticlesService;

class AtomFeedBuilder
{
    public function __construct(private ArticlesService $articles) {}

    public function build(): string
    {
        $articles = $this->articles->discover(config('articles.discovery.feed', ['published']));

        return $this->renderXml($articles);
    }

    /**
     * @param  list<array<string, mixed>>  $articles
     */
    private function renderXml(array $articles): string
    {
        $host = rtrim((string) config('articles.seo.canonical_host'), '/');
        $feedPath = (string) config('articles.feed.path', '/feed.xml');
        $urlPrefix = (string) config('articles.url_prefix', '/articles');
        $author = config('articles.seo.author', []);
        $authorName = (string) ($author['name'] ?? 'Author');
        $authorUri = $author['url'] ?? null;

        $updated = $this->feedUpdated($articles);
        $selfUrl = $host.$feedPath;
        $siteUrl = $host.rtrim($urlPrefix, '/');

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";
        $xml .= '  <title>'.$this->escape((string) config('articles.feed.title'))."</title>\n";
        $xml .= '  <subtitle>'.$this->escape((string) config('articles.feed.subtitle'))."</subtitle>\n";
        $xml .= '  <link rel="self" type="application/atom+xml" href="'.$this->escape($selfUrl)."\"/>\n";
        $xml .= '  <link rel="alternate" type="text/html" href="'.$this->escape($siteUrl)."\"/>\n";
        $xml .= '  <id>'.$this->escape($siteUrl)."</id>\n";
        $xml .= '  <updated>'.$this->escape($updated)."</updated>\n";
        $xml .= "  <author>\n";
        $xml .= '    <name>'.$this->escape($authorName)."</name>\n";
        if ($authorUri) {
            $xml .= '    <uri>'.$this->escape((string) $authorUri)."</uri>\n";
        }
        $xml .= "  </author>\n";

        foreach ($articles as $article) {
            $xml .= $this->renderEntry($article, $host, $urlPrefix, $authorName);
        }

        $xml .= '</feed>';

        return $xml;
    }

    /**
     * @param  array<string, mixed>  $article
     */
    private function renderEntry(array $article, string $host, string $urlPrefix, string $authorName): string
    {
        $slug = (string) $article['slug'];
        $url = $host.rtrim($urlPrefix, '/')."/{$slug}";
        $title = (string) ($article['title'] ?? $slug);
        $summary = (string) ($article['description'] ?? '');
        $publishedAt = $article['publishedAt'] ?? null;
        $updated = $this->normalizeDate(is_string($publishedAt) ? $publishedAt : null)
            ?? $this->articleMtime($slug)
            ?? gmdate(DATE_ATOM);
        $published = $this->normalizeDate(is_string($publishedAt) ? $publishedAt : null);

        $entry = "  <entry>\n";
        $entry .= '    <title>'.$this->escape($title)."</title>\n";
        $entry .= '    <link rel="alternate" type="text/html" href="'.$this->escape($url)."\"/>\n";
        $entry .= '    <id>'.$this->escape($url)."</id>\n";
        $entry .= '    <updated>'.$this->escape($updated)."</updated>\n";
        if ($published) {
            $entry .= '    <published>'.$this->escape($published)."</published>\n";
        }
        if ($summary !== '') {
            $entry .= '    <summary>'.$this->escape($summary)."</summary>\n";
        }
        $entry .= "    <author>\n";
        $entry .= '      <name>'.$this->escape($authorName)."</name>\n";
        $entry .= "    </author>\n";
        $entry .= "  </entry>\n";

        return $entry;
    }

    /**
     * @param  list<array<string, mixed>>  $articles
     */
    private function feedUpdated(array $articles): string
    {
        $latest = null;

        foreach ($articles as $article) {
            $publishedAt = $article['publishedAt'] ?? null;
            $candidate = $this->normalizeDate(is_string($publishedAt) ? $publishedAt : null)
                ?? $this->articleMtime((string) $article['slug']);

            if ($candidate && ($latest === null || $candidate > $latest)) {
                $latest = $candidate;
            }
        }

        return $latest ?? gmdate(DATE_ATOM);
    }

    private function articleMtime(string $slug): ?string
    {
        $path = $this->articles->articlePath($slug);

        if (! file_exists($path)) {
            return null;
        }

        $mtime = filemtime($path);

        return $mtime ? gmdate(DATE_ATOM, $mtime) : null;
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (\Throwable) {
            return null;
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
