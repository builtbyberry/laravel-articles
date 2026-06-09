<?php

use BuiltByBerry\LaravelArticles\Services\ArticlesService;

test('renders the articles index', function () {
    $this->get(route('articles'))->assertOk();
});

test('renders a ready article by slug', function () {
    $response = $this->get(route('articles.show', ['slug' => 'sample-article']));

    $response->assertOk();
    $response->assertSee('Sample Article Title', false);
    $response->assertSee('data-slug="sample-article"', false);
});

test('returns 404 for an unknown article slug', function () {
    $this->get('/articles/this-article-does-not-exist')->assertNotFound();
});

test('strips frontmatter and channel notes from rendered html', function () {
    $service = app(ArticlesService::class);

    ['html' => $html, 'meta' => $meta] = $service->render('sample-article');

    expect($meta['title'] ?? null)->toBe('Sample Article Title');
    expect($meta['kind'] ?? null)->toBe('evergreen-explainer');
    expect($html)->not->toContain('Channel notes');
    expect($html)->not->toContain('LinkedIn cut');
    expect($html)->toContain('When to reach for');
});

test('discover hides draft articles from the index by default', function () {
    $service = app(ArticlesService::class);
    $slugs = collect($service->discover())->pluck('slug')->all();

    expect($slugs)->toContain('sample-article');
    expect($slugs)->toContain('published-article');
    expect($slugs)->not->toContain('draft-article');

    foreach ($service->discover() as $card) {
        expect($card['status'])->toBeIn(['ready', 'published']);
    }
});
