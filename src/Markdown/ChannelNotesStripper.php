<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

class ChannelNotesStripper
{
    private ChannelNotesParser $parser;

    public function __construct(?ChannelNotesParser $parser = null)
    {
        $this->parser = $parser ?? new ChannelNotesParser;
    }

    /**
     * Drop everything from a configured section heading (e.g. `## Channel notes`)
     * onward, plus the `---` separator that typically precedes it.
     *
     * The headings are read from `articles.strip_sections`. Matching is
     * heading-prefix based and whitespace-insensitive between words. The source
     * file is never touched — only the rendered body.
     */
    public function strip(string $body): string
    {
        $headings = array_values(array_filter(
            (array) config('articles.strip_sections', ['Channel notes']),
            fn (mixed $heading): bool => is_string($heading) && $heading !== '',
        ));

        if ($headings === []) {
            return $body;
        }

        $sectionOffset = $this->parser->sectionOffset($body, $headings);

        if ($sectionOffset === null) {
            return $body;
        }

        $beforeSection = substr($body, 0, $sectionOffset);
        $withoutSeparator = preg_replace('/(?:\r?\n)?---\s*\r?\n\z/', "\n", $beforeSection);

        return $withoutSeparator ?? $beforeSection;
    }
}
