<?php

use BuiltByBerry\LaravelArticles\Markdown\FrontmatterParser;

beforeEach(function () {
    $this->parser = new FrontmatterParser;
});

test('parses frontmatter into a metadata array and body', function () {
    $raw = <<<'MD'
    ---
    title: Hello World
    status: published
    tags:
      - laravel
      - markdown
    ---
    # Body

    Some content.
    MD;

    [$meta, $body] = $this->parser->parse($raw);

    expect($meta)->toMatchArray([
        'title' => 'Hello World',
        'status' => 'published',
        'tags' => ['laravel', 'markdown'],
    ]);
    expect($body)->toContain('# Body');
    expect($body)->not->toContain('title: Hello World');
});

test('returns empty metadata and the raw string when there is no frontmatter', function () {
    $raw = "# Just a heading\n\nNo frontmatter here.";

    [$meta, $body] = $this->parser->parse($raw);

    expect($meta)->toBe([]);
    expect($body)->toBe($raw);
});

test('returns empty metadata but preserves the body when frontmatter YAML is malformed', function () {
    $raw = <<<'MD'
    ---
    title: "unterminated
    : : :
    ---
    Body survives.
    MD;

    [$meta, $body] = $this->parser->parse($raw);

    expect($meta)->toBe([]);
    expect($body)->toContain('Body survives.');
});

test('coerces non-array frontmatter (a bare scalar) to an empty array', function () {
    $raw = "---\njust-a-string\n---\nBody.";

    [$meta, $body] = $this->parser->parse($raw);

    expect($meta)->toBe([]);
    expect($body)->toContain('Body.');
});

test('handles CRLF line endings around the frontmatter fence', function () {
    $raw = "---\r\ntitle: Windows\r\n---\r\nBody.";

    [$meta, $body] = $this->parser->parse($raw);

    expect($meta)->toMatchArray(['title' => 'Windows']);
    expect($body)->toContain('Body.');
});
