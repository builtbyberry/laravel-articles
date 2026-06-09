<?php

namespace BuiltByBerry\LaravelArticles\Tests;

use BuiltByBerry\LaravelArticles\ArticlesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ArticlesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $fixturesPath = __DIR__.'/Fixtures/articles';
        $viewsPath = __DIR__.'/Fixtures/views';

        $app['config']->set('articles.content_path', $fixturesPath);
        $app['config']->set('articles.url_prefix', '/articles');
        $app['config']->set('articles.views.index', 'articles.index');
        $app['config']->set('articles.views.show', 'articles.show');
        $app['config']->set('articles.views.series', 'articles.series');
        $app['config']->set('articles.series.path', '_series');
        $app['config']->set('articles.series.url_prefix', '/articles/series');
        $app['config']->set('articles.seo.canonical_host', 'https://example.test');
        $app['config']->set('articles.seo.index_title', 'Articles');
        $app['config']->set('articles.seo.index_description', 'Test articles.');
        $app['config']->set('articles.seo.author.name', 'Test Author');
        $app['config']->set('articles.seo.author.url', 'https://example.test/about');
        $app['config']->set('articles.seo.publisher.name', 'Example');
        $app['config']->set('articles.seo.publisher.url', 'https://example.test');
        $app['config']->set('articles.seo.publisher.logo', '/favicon.svg');
        $app['config']->set('articles.feed.title', 'Example — Articles');
        $app['config']->set('articles.feed.subtitle', 'Test feed.');
        $app['config']->set('articles.feed.path', '/feed.xml');
        $app['config']->set('articles.sitemap.path', '/sitemap.xml');
        $app['config']->set('articles.routes.enabled', true);
        $app['config']->set('articles.routes.middleware', ['web']);

        $app['view']->addLocation($viewsPath);
    }
}
