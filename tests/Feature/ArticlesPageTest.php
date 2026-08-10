<?php

use BuiltByBerry\LaravelArticles\Markdown\ChannelNotesStripper;
use BuiltByBerry\LaravelArticles\Markdown\FrontmatterParser;
use BuiltByBerry\LaravelArticles\Markdown\MarkdownRenderer;
use BuiltByBerry\LaravelArticles\Services\ArticlesService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

test('retrieves a channel note with canonical article metadata', function () {
    $note = app(ArticlesService::class)->channelNote('sample-article', 'Newsletter pitch');

    expect($note)
        ->slug->toBe('sample-article')
        ->title->toBe('Sample Article Title')
        ->description->toBe('A sample article for package tests.')
        ->status->toBe('ready')
        ->url->toBe('/articles/sample-article')
        ->key->toBe('newsletter-pitch')
        ->markdown->toContain('**practical breakdown**');
});

test('channel notes respect status filters and safe article slugs', function () {
    expect(app(ArticlesService::class)->channelNote('sample-article', 'missing'))->toBeNull();

    $this->get('/articles/../sample-article')->assertNotFound();

    expect(fn () => app(ArticlesService::class)->channelNote('draft-article', 'newsletter-pitch', ['published']))
        ->toThrow(NotFoundHttpException::class)
        ->and(fn () => app(ArticlesService::class)->channelNote('../sample-article', 'newsletter-pitch'))
        ->toThrow(NotFoundHttpException::class);
});

test('article path safety preserves existing single-directory slug names', function () {
    $service = app(ArticlesService::class);

    expect($service->articlePath('Legacy_Article'))
        ->toEndWith('/Legacy_Article/article.md')
        ->and(fn () => $service->articlePath('nested/article'))
        ->toThrow(NotFoundHttpException::class)
        ->and(fn () => $service->articlePath('nested\\article'))
        ->toThrow(NotFoundHttpException::class);
});

test('articles service remains compatible with its original constructor', function () {
    $service = new ArticlesService(
        app(MarkdownRenderer::class),
        app(FrontmatterParser::class),
        new ChannelNotesStripper,
    );

    expect($service->channelNote('sample-article', 'newsletter-pitch'))
        ->key->toBe('newsletter-pitch');
});
