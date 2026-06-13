<?php

use BuiltByBerry\LaravelArticles\Support\SeoMeta;

test('emits article SEO with og_image override and frontmatter publish date', function () {
    $this->get(route('articles.show', ['slug' => 'published-article']));

    $seo = app(SeoMeta::class);

    expect($seo->ogType())->toBe('article');
    expect($seo->canonical())->toBe('https://example.test/articles/published-article');
    expect($seo->ogImage())->toBe('https://example.test/images/og/articles/published-article.png');

    // datePublished comes from frontmatter (`published_at: 2026-05-01`), so it is
    // deterministic. dateModified is derived from git/mtime and is not pinned.
    $schema = $seo->articleSchema();
    expect($schema)->toHaveKey('datePublished');
    expect($schema['datePublished'])->toBe('2026-05-01');
    expect($schema['@type'])->toBe('Article');
    expect($schema['url'])->toBe('https://example.test/articles/published-article');
});

test('falls back to the default og image when frontmatter has none', function () {
    $this->get(route('articles.show', ['slug' => 'sample-article']));

    $seo = app(SeoMeta::class);

    expect($seo->ogImage())->toBe('https://example.test/images/og/site-default.png');
});
