<?php

use BuiltByBerry\LaravelArticles\Markdown\MarkdownRenderer;

beforeEach(function () {
    $this->renderer = new MarkdownRenderer;
});

test('renders CommonMark to HTML', function () {
    $html = $this->renderer->toHtml('This is **bold** and *italic*.');

    expect($html)->toContain('<strong>bold</strong>');
    expect($html)->toContain('<em>italic</em>');
    expect($html)->toContain('<p>');
});

test('renders fenced code with a data-lang attribute', function () {
    $html = $this->renderer->toHtml("```php\n<?php echo 'hi';\n```");

    expect($html)->toContain('data-lang="php"');
    expect($html)->toContain('<pre');
    // Code is HTML-escaped, not executed as markup.
    expect($html)->toContain('&lt;?php');
});

test('adds permalink ids to h2 and h3 headings', function () {
    $html = $this->renderer->toHtml("## Getting Started\n\n### Details");

    expect($html)->toContain('id="getting-started"');
    expect($html)->toContain('id="details"');
});

test('extracts a table of contents from rendered headings', function () {
    $html = '<h2 id="intro">Introduction</h2><p>x</p><h3 id="setup">Setup steps</h3>';

    $toc = $this->renderer->extractToc($html);

    expect($toc)->toBe([
        ['level' => 2, 'id' => 'intro', 'text' => 'Introduction'],
        ['level' => 3, 'id' => 'setup', 'text' => 'Setup steps'],
    ]);
});

test('estimates reading minutes at roughly 220 words per minute', function () {
    $words = str_repeat('word ', 440);

    expect($this->renderer->readingMinutes($words))->toBe(2);
});

test('never reports fewer than one reading minute', function () {
    expect($this->renderer->readingMinutes('just a few words'))->toBe(1);
    expect($this->renderer->readingMinutes(''))->toBe(1);
});

test('excludes fenced code blocks from the reading-time word count', function () {
    $prose = str_repeat('word ', 110);
    $withCode = $prose."\n\n```\n".str_repeat('code ', 5000)."\n```\n";

    // 110 prose words → still 1 minute; the 5000 code words must not inflate it.
    expect($this->renderer->readingMinutes($withCode))->toBe(1);
});

test('rewrites relative .md links to prefixed slug URLs', function () {
    $markdown = 'See [the next part](second-slug.md) for more.';

    $result = $this->renderer->rewriteLinks($markdown, '/articles');

    expect($result)->toBe('See [the next part](/articles/second-slug) for more.');
});

test('preserves anchors when rewriting .md links', function () {
    $markdown = '[jump](guide.md#installation)';

    $result = $this->renderer->rewriteLinks($markdown, '/articles/');

    expect($result)->toBe('[jump](/articles/guide#installation)');
});

test('leaves non-markdown links untouched', function () {
    $markdown = '[external](https://example.com) and [image](photo.png)';

    $result = $this->renderer->rewriteLinks($markdown, '/articles');

    expect($result)->toBe($markdown);
});

test('rewrites folder-style article.md links using the parent directory slug', function () {
    $markdown = 'See [the next part](../second-slug/article.md) for more.';

    $result = $this->renderer->rewriteLinks($markdown, '/articles');

    expect($result)->toBe('See [the next part](/articles/second-slug) for more.');
});

test('preserves anchors on folder-style links', function () {
    $markdown = '[jump](../guide/article.md#installation)';

    $result = $this->renderer->rewriteLinks($markdown, '/articles');

    expect($result)->toBe('[jump](/articles/guide#installation)');
});

test('escapes raw HTML when html_input is set to escape', function () {
    config()->set('articles.markdown.html_input', 'escape');

    $renderer = new MarkdownRenderer;
    $html = $renderer->toHtml('Hello <script>alert(1)</script> world');

    expect($html)->not->toContain('<script>');
    expect($html)->toContain('&lt;script&gt;');
});

test('falls back to allow when html_input is misconfigured', function () {
    config()->set('articles.markdown.html_input', 'escaped'); // typo, not a valid value

    $renderer = new MarkdownRenderer;

    // Invalid value must not crash the CommonMark environment; raw HTML passes
    // through as it does under the default 'allow'.
    expect($renderer->toHtml('<div>raw</div>'))->toContain('<div>raw</div>');
});

test('uses file mtime and skips git when use_git is disabled', function () {
    config()->set('articles.last_updated.use_git', false);

    $path = __DIR__.'/../Fixtures/articles/published-article/article.md';
    $expected = date(DATE_ATOM, filemtime($path));

    // With git disabled the result is the file mtime in ATOM format — not the
    // git commit date the shell path would return.
    expect($this->renderer->lastUpdated($path))->toBe($expected);
});

test('returns null last-updated for a missing file when use_git is disabled', function () {
    config()->set('articles.last_updated.use_git', false);

    expect($this->renderer->lastUpdated('/no/such/article.md'))->toBeNull();
});
