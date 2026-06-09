<?php

use BuiltByBerry\LaravelArticles\Http\Controllers\ArticlesController;
use BuiltByBerry\LaravelArticles\Http\Controllers\FeedController;
use BuiltByBerry\LaravelArticles\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

$prefix = trim((string) config('articles.url_prefix', '/articles'), '/');
$routeNames = config('articles.route_names', []);

Route::get($prefix === '' ? '/' : "/{$prefix}", [ArticlesController::class, 'index'])
    ->name($routeNames['index'] ?? 'articles');

Route::get(($prefix === '' ? '' : "/{$prefix}").'/{slug}', [ArticlesController::class, 'show'])
    ->name($routeNames['show'] ?? 'articles.show')
    ->where('slug', '[a-z0-9\-]+');

if (config('articles.feed.enabled', true)) {
    Route::get((string) config('articles.feed.path', '/feed.xml'), FeedController::class)
        ->name($routeNames['feed'] ?? 'articles.feed');
}

if (config('articles.sitemap.enabled', true)) {
    Route::get((string) config('articles.sitemap.path', '/sitemap.xml'), SitemapController::class)
        ->name($routeNames['sitemap'] ?? 'articles.sitemap');
}
