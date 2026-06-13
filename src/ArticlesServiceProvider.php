<?php

namespace BuiltByBerry\LaravelArticles;

use BuiltByBerry\LaravelArticles\Console\GenerateOgImagesCommand;
use BuiltByBerry\LaravelArticles\Feed\AtomFeedBuilder;
use BuiltByBerry\LaravelArticles\Markdown\MarkdownRenderer;
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

        // MarkdownRenderer is stateless and expensive to build (it assembles a
        // CommonMark environment), so it stays a true singleton.
        $this->app->singleton(MarkdownRenderer::class);

        // These carry per-request state (SeoMeta) or per-request memo caches
        // (SeriesService), so they are bound as `scoped` — identical to a
        // singleton within one request, but flushed by Octane between requests.
        $this->app->scoped(SeoMeta::class);
        $this->app->scoped(SeriesService::class);
        $this->app->scoped(ArticlesService::class);
        $this->app->scoped(AtomFeedBuilder::class);
        $this->app->scoped(ArticlesSitemapProvider::class);

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
