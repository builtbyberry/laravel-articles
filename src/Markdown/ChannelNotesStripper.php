<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

class ChannelNotesStripper
{
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
            fn ($heading): bool => is_string($heading) && $heading !== '',
        ));

        if ($headings === []) {
            return $body;
        }

        $alternatives = array_map(function (string $heading): string {
            $words = array_map(
                fn (string $word): string => preg_quote($word, '/'),
                preg_split('/\s+/', trim($heading)) ?: [],
            );

            return implode('\s+', $words);
        }, $headings);

        $pattern = '/\n(?:---\s*\n)?##\s+(?:'.implode('|', $alternatives).')\b.*$/is';

        return preg_replace($pattern, "\n", $body) ?? $body;
    }
}
