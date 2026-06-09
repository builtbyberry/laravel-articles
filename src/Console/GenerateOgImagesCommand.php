<?php

namespace BuiltByBerry\LaravelArticles\Console;

use BuiltByBerry\LaravelArticles\Services\ArticlesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class GenerateOgImagesCommand extends Command
{
    protected $signature = 'articles:og-generate
                            {slug? : Limit generation to a single article slug}
                            {--force : Overwrite existing PNGs without prompting}';

    protected $description = 'Render per-article OG cards to public/images/og/articles/.';

    public function handle(ArticlesService $articles): int
    {
        if (! class_exists(Browsershot::class)) {
            $this->error('Spatie\\Browsershot is not installed. Run: composer require --dev spatie/browsershot');

            return self::FAILURE;
        }

        if (! app()->environment('local', 'testing')) {
            $this->error('articles:og-generate is a local-only tool. Run it on your dev machine and commit the resulting PNGs.');

            return self::FAILURE;
        }

        $outputDir = public_path((string) config('articles.og.output_dir', 'images/og/articles'));
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
            $this->error("Could not create output directory: {$outputDir}");

            return self::FAILURE;
        }

        $only = $this->argument('slug');
        $cards = $articles->discover(['draft', 'ready', 'published']);

        if ($only !== null) {
            $cards = array_values(array_filter($cards, fn (array $card): bool => $card['slug'] === $only));
            if ($cards === []) {
                $this->error("No article found with slug: {$only}");

                return self::FAILURE;
            }
        }

        if ($cards === []) {
            $this->warn('No articles to generate.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Rendering %d article card%s…', count($cards), count($cards) === 1 ? '' : 's'));

        foreach ($cards as $card) {
            $outputPath = "{$outputDir}/{$card['slug']}.png";

            if (file_exists($outputPath) && ! $this->option('force')) {
                if (! $this->confirm("Overwrite existing {$card['slug']}.png?", true)) {
                    $this->line("  skip {$card['slug']}");

                    continue;
                }
            }

            $this->renderCard($card, $outputPath);
            $this->line("  done {$card['slug']}");
        }

        $this->newLine();
        $this->info('Commit the new PNGs in public/'.config('articles.og.output_dir').' and push.');

        return self::SUCCESS;
    }

    /**
     * @param  array{slug: string, title: string, kind: ?string, readingMinutes: ?int}  $card
     */
    private function renderCard(array $card, string $outputPath): void
    {
        $kindLabels = config('articles.og.kind_labels', []);
        $kindLabel = $kindLabels[$card['kind'] ?? ''] ?? null;
        $eyebrow = $kindLabel ? mb_strtoupper((string) $kindLabel) : '';

        $html = View::make((string) config('articles.og.view', 'laravel-articles::og.article-card'), [
            'title' => $card['title'],
            'eyebrow' => $eyebrow,
            'readingMinutes' => $card['readingMinutes'] ?? null,
            'siteName' => config('articles.seo.site_name'),
            'footerUrl' => rtrim((string) config('articles.seo.canonical_host'), '/').config('articles.url_prefix'),
        ])->render();

        Browsershot::html($html)
            ->windowSize(1200, 630)
            ->setScreenshotType('png')
            ->fullPage()
            ->waitUntilNetworkIdle()
            ->save($outputPath);
    }
}
