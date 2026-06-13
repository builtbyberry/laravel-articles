<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class MarkdownRenderer
{
    protected MarkdownConverter $converter;

    public function __construct()
    {
        $htmlInput = (string) config('articles.markdown.html_input', 'allow');
        if (! in_array($htmlInput, ['allow', 'escape', 'strip'], true)) {
            $htmlInput = 'allow';
        }

        $environment = new Environment([
            'html_input' => $htmlInput,
            'allow_unsafe_links' => (bool) config('articles.markdown.allow_unsafe_links', true),
            'heading_permalink' => [
                'html_class' => 'heading-anchor',
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'heading_class' => '',
                'fragment_prefix' => '',
                'insert' => 'before',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'title' => 'Link to this section',
                'symbol' => '#',
                'aria_hidden' => false,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new StrikethroughExtension);
        $environment->addExtension(new AutolinkExtension);
        $environment->addExtension(new HeadingPermalinkExtension);
        $environment->addRenderer(FencedCode::class, new class implements NodeRendererInterface
        {
            public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
            {
                /** @var FencedCode $node */
                $infoWords = $node->getInfoWords();
                $lang = $infoWords[0] ?? '';
                $attrs = $lang ? ['data-lang' => $lang] : [];
                $code = htmlspecialchars($node->getLiteral(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                $codeEl = new HtmlElement('code', [], $code);

                return new HtmlElement('pre', $attrs, $codeEl);
            }
        });

        $this->converter = new MarkdownConverter($environment);
    }

    public function toHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }

    /**
     * @return list<array{level: int, id: string, text: string}>
     */
    public function extractToc(string $html): array
    {
        preg_match_all('/<h([23])\s+id="([^"]+)"[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER);

        return array_map(function (array $match): array {
            return [
                'level' => (int) $match[1],
                'id' => $match[2],
                'text' => trim(strip_tags($match[3])),
            ];
        }, $matches);
    }

    public function readingMinutes(string $markdown): int
    {
        $stripped = preg_replace('/```.*?```/s', ' ', $markdown) ?? $markdown;
        $stripped = preg_replace('/~~~.*?~~~/s', ' ', $stripped) ?? $stripped;
        $stripped = preg_replace('/`[^`]*`/', ' ', $stripped) ?? $stripped;
        $stripped = preg_replace('/[#>*_\-\[\]()!]+/', ' ', $stripped) ?? $stripped;

        $words = preg_split('/\s+/', trim($stripped)) ?: [];
        $count = count(array_filter($words, fn (string $word): bool => $word !== ''));

        return max(1, (int) ceil($count / 220));
    }

    public function lastUpdated(string $path, string $cachePrefix = 'articles'): ?string
    {
        $mtime = @filemtime($path);

        if (! config('articles.last_updated.use_git', true)) {
            return $mtime ? date(DATE_ATOM, $mtime) : null;
        }

        $cacheKey = "{$cachePrefix}.last_updated:".md5($path).':'.($mtime ?: '0');
        $ttl = (int) config('articles.last_updated.cache_ttl', 86400);

        return Cache::remember($cacheKey, $ttl, function () use ($path, $mtime): ?string {
            $relative = ltrim(str_replace(base_path(), '', $path), '/');
            $command = sprintf(
                'git -C %s log -1 --format=%%cI -- %s 2>/dev/null',
                escapeshellarg(base_path()),
                escapeshellarg($relative)
            );
            $output = trim((string) @shell_exec($command));

            if ($output !== '') {
                return $output;
            }

            return $mtime ? date(DATE_ATOM, $mtime) : null;
        });
    }

    public function rewriteLinks(string $markdown, string $urlPrefix): string
    {
        return preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+\.md)([^)]*)\)/',
            function (array $matches) use ($urlPrefix): string {
                $text = $matches[1];
                $href = $matches[2];
                $anchor = $matches[3];

                // Articles live at `<slug>/article.md`, so a folder-style link
                // (`../other-slug/article.md`, which also resolves on GitHub)
                // takes its slug from the parent directory. A flat link
                // (`other-slug.md`) takes its slug from the filename.
                $filename = pathinfo(basename($href), PATHINFO_FILENAME);
                $slug = $filename === 'article'
                    ? basename(dirname($href))
                    : $filename;
                $url = rtrim($urlPrefix, '/')."/{$slug}";

                return "[{$text}]({$url}{$anchor})";
            },
            $markdown
        ) ?? $markdown;
    }
}
