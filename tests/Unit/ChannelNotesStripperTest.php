<?php

use BuiltByBerry\LaravelArticles\Markdown\ChannelNotesStripper;

beforeEach(function () {
    $this->stripper = new ChannelNotesStripper;
});

test('strips the default Channel notes section and a preceding separator', function () {
    $body = "Intro paragraph.\n\n---\n## Channel notes\n\nInternal only.";

    $result = $this->stripper->strip($body);

    expect($result)->toContain('Intro paragraph.');
    expect($result)->not->toContain('Channel notes');
    expect($result)->not->toContain('Internal only.');
});

test('matches headings whitespace-insensitively', function () {
    $body = "Body.\n\n##   Channel    notes\n\nGone.";

    expect($this->stripper->strip($body))->not->toContain('Gone.');
});

test('strips a custom configured heading', function () {
    config()->set('articles.strip_sections', ['Editor scratchpad']);

    $body = "Body.\n\n## Editor scratchpad\n\nDrafting.";

    $result = $this->stripper->strip($body);

    expect($result)->toContain('Body.');
    expect($result)->not->toContain('Drafting.');
});

test('leaves the body untouched when strip_sections is empty', function () {
    config()->set('articles.strip_sections', []);

    $body = "Body.\n\n## Channel notes\n\nStill here.";

    expect($this->stripper->strip($body))->toBe($body);
});

test('does not strip non-configured headings', function () {
    $body = "Body.\n\n## Appendix\n\nKept.";

    expect($this->stripper->strip($body))->toContain('Kept.');
});

test('does not treat a heading inside a fenced block as a stripped section', function () {
    $body = "Body.\n\n```markdown\n## Channel notes\n\nExample.\n```\n\nKept.";

    expect($this->stripper->strip($body))->toBe($body);
});

test('does not close a fenced block on a fence with an info string', function () {
    $body = "```markdown\n```php\n## Channel notes\n\nExample.\n```";

    expect($this->stripper->strip($body))->toBe($body);
});
