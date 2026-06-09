<?php

namespace BuiltByBerry\LaravelArticles;

use BuiltByBerry\LaravelArticles\Console\GenerateOgImagesCommand;
use BuiltByBerry\LaravelArticles\Feed\AtomFeedBuilder;
use BuiltByBerry\LaravelArticles\Services\ArticlesService;
use BuiltByBerry\LaravelArticles\Services\SeriesService;
use BuiltByBerry\LaravelArticles\Sitemap\ArticlesSitemapProvider;
use BuiltByBerry\LaravelArticles\Support\SeoMeta;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/articles.php', 'articles');

        $this->app->singleton(SeoMeta::class);
        $this->app->singleton(SeriesService::class);
        $this->app->singleton(ArticlesService::class);
        $this->app->singleton(AtomFeedBuilder::class);
        $this->app->singleton(ArticlesSitemapProvider::class);

        $this->app->afterResolving(ArticlesService::class, function (ArticlesService $articles): void {
            $articles->setSeriesService($this->app->make(SeriesService::class));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-articles');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/articles.php' => config_path('articles.php'),
            ], 'articles-config');

            $this->commands([
                GenerateOgImagesCommand::class,
            ]);
        }

        if (config('articles.routes.enabled', true)) {
            Route::middleware(config('articles.routes.middleware', ['web']))
                ->group(__DIR__.'/../routes/articles.php');
        }
    }
}
