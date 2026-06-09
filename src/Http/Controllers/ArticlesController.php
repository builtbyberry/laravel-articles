<?php

namespace BuiltByBerry\LaravelArticles\Http\Controllers;

use BuiltByBerry\LaravelArticles\Services\ArticlesService;
use BuiltByBerry\LaravelArticles\Support\SeoMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ArticlesController extends Controller
{
    public function __construct(
        private ArticlesService $articles,
        private SeoMeta $seo,
    ) {}

    public function index(): View
    {
        $host = rtrim((string) config('articles.seo.canonical_host'), '/');
        $urlPrefix = (string) config('articles.url_prefix', '/articles');

        $this->seo->set([
            'title' => (string) config('articles.seo.index_title', 'Articles'),
            'description' => (string) config('articles.seo.index_description', 'Articles'),
            'ogType' => 'website',
            'canonical' => $host.rtrim($urlPrefix, '/'),
        ]);

        return view((string) config('articles.views.index', 'articles.index'), [
            'articles' => $this->articles->discover(),
        ]);
    }

    public function show(string $slug): View
    {
        $payload = $this->articles->render($slug);
        $this->applySeoForArticle($slug, $payload['meta'], $payload['lastUpdated']);

        return view((string) config('articles.views.show', 'articles.show'), [
            'slug' => $slug,
            ...$payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function applySeoForArticle(string $slug, array $meta, ?string $lastUpdated): void
    {
        $host = rtrim((string) config('articles.seo.canonical_host'), '/');
        $urlPrefix = (string) config('articles.url_prefix', '/articles');
        $author = config('articles.seo.author', []);
        $publisher = config('articles.seo.publisher', []);

        $title = (string) ($meta['title'] ?? 'Article');
        $description = (string) ($meta['description'] ?? config('articles.seo.index_description', ''));
        $canonical = $host.rtrim($urlPrefix, '/')."/{$slug}";
        $defaultOg = (string) config('articles.seo.default_og_image', '/images/og/site-default.png');
        $ogImage = ! empty($meta['og_image'])
            ? $host.(string) $meta['og_image']
            : $host.$defaultOg;

        $datePublished = $meta['published_at'] ?? $lastUpdated;
        $dateModified = $lastUpdated ?? $meta['published_at'] ?? null;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => [$ogImage],
            'url' => $canonical,
            'author' => [
                '@type' => 'Person',
                'name' => (string) ($author['name'] ?? 'Author'),
                'url' => $author['url'] ?? null,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => (string) ($publisher['name'] ?? config('app.name')),
                'url' => (string) ($publisher['url'] ?? $host),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $host.(string) ($publisher['logo'] ?? '/favicon.svg'),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],
        ];

        if ($datePublished) {
            $schema['datePublished'] = $datePublished;
        }

        if ($dateModified) {
            $schema['dateModified'] = $dateModified;
        }

        $this->seo->set([
            'title' => $title,
            'description' => $description,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'canonical' => $canonical,
            'articleSchema' => $schema,
            'articleAuthor' => (string) ($author['name'] ?? null),
            'articlePublishedTime' => is_string($datePublished) ? $datePublished : null,
            'articleModifiedTime' => is_string($dateModified) ? $dateModified : null,
        ]);
    }
}
