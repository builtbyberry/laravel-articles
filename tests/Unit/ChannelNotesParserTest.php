<?php

use BuiltByBerry\LaravelArticles\Markdown\ChannelNotesParser;

beforeEach(function () {
    $this->parser = new ChannelNotesParser;
});

test('extracts normalized channel notes while preserving markdown', function () {
    $body = <<<'MARKDOWN'
# Article

Body.

## Channel notes

### Newsletter pitch

Read the **full article** and [share it](https://example.test).

### LinkedIn cut
Post this.
MARKDOWN;

    expect($this->parser->extract($body))
        ->toMatchArray([
            'newsletter-pitch' => [
                'key' => 'newsletter-pitch',
                'heading' => 'Newsletter pitch',
                'markdown' => 'Read the **full article** and [share it](https://example.test).',
            ],
            'linkedin-cut' => [
                'key' => 'linkedin-cut',
                'heading' => 'LinkedIn cut',
                'markdown' => 'Post this.',
            ],
        ]);
});

test('ignores fake headings inside fenced code blocks', function () {
    $body = <<<'MARKDOWN'
```markdown
## Channel notes
### Fake note
```

## Channel notes

### Newsletter pitch

Keep this example:

```markdown
### Not another note
```
MARKDOWN;

    $notes = $this->parser->extract($body);

    expect($notes)->toHaveKey('newsletter-pitch')
        ->and($notes)->not->toHaveKey('fake-note')
        ->and($notes)->not->toHaveKey('not-another-note')
        ->and($notes['newsletter-pitch']['markdown'])->toContain('### Not another note');
});

test('does not close a fenced block on a fence with an info string', function () {
    $body = <<<'MARKDOWN'
## Channel notes

### Newsletter pitch

```markdown
```php
### Still code
```
MARKDOWN;

    $notes = $this->parser->extract($body);

    expect($notes)->toHaveCount(1)
        ->and($notes)->toHaveKey('newsletter-pitch')
        ->and($notes['newsletter-pitch']['markdown'])->toContain('### Still code');
});

test('keeps the first duplicate normalized heading and ignores malformed headings', function () {
    $body = <<<'MARKDOWN'
## Channel notes

### Newsletter pitch
First.

### Newsletter Pitch!
Second.

###
Malformed.
MARKDOWN;

    expect($this->parser->extract($body))
        ->toHaveCount(1)
        ->and($this->parser->extract($body)['newsletter-pitch']['markdown'])->toBe('First.');
});

test('does not append malformed heading content to the preceding note', function () {
    $body = "## Channel notes\n\n### Newsletter pitch\nKeep.\n\n###\nDiscard.";

    expect($this->parser->extract($body)['newsletter-pitch']['markdown'])->toBe('Keep.');
});

test('returns no notes when the terminal section is absent', function () {
    expect($this->parser->extract("# Article\n\nBody."))->toBe([]);
});
