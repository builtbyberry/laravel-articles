<?php

use BuiltByBerry\LaravelArticles\Services\SeriesService;

test('discovers series manifests and resolves ordered articles', function () {
    $service = app(SeriesService::class);

    $sections = $service->discoverForIndex();

    expect($sections)->not->toBeEmpty();
    expect($sections[0]['slug'])->toBe('sample-series');
    expect(collect($sections[0]['articles'])->pluck('slug')->all())
        ->toBe(['sample-article', 'published-article']);
});

test('context for article includes prev and next links', function () {
    $service = app(SeriesService::class);

    $context = $service->contextForArticle('published-article');

    expect($context)->not->toBeNull();
    expect($context['slug'])->toBe('sample-series');
    expect($context['part'])->toBe(2);
    expect($context['total'])->toBe(2);
    expect($context['prev']['slug'])->toBe('sample-article');
    expect($context['next'])->toBeNull();
});

test('first article in series has next but no prev', function () {
    $context = app(SeriesService::class)->contextForArticle('sample-article');

    expect($context['prev'])->toBeNull();
    expect($context['next']['slug'])->toBe('published-article');
});

test('draft articles are omitted from public series', function () {
    $series = app(SeriesService::class)->resolveSeries('sample-series');

    expect(collect($series['articles'])->pluck('slug')->all())
        ->not->toContain('draft-article');
});

test('renders series landing page', function () {
    $this->get(route('articles.series', ['series' => 'sample-series']))
        ->assertOk()
        ->assertSee('data-series="sample-series"', false)
        ->assertSee('sample-article', false)
        ->assertSee('published-article', false);
});

test('returns 404 for unknown series', function () {
    $this->get('/articles/series/unknown-series')->assertNotFound();
});

test('series route is not captured as article slug', function () {
    $this->get('/articles/series/sample-series')->assertOk();
    $this->get('/articles/series')->assertNotFound();
});

test('sitemap includes series landing url', function () {
    $body = $this->get('/sitemap.xml')->getContent();

    expect($body)->toContain('/articles/series/sample-series');
});
