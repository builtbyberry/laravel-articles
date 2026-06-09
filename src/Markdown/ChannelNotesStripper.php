<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

class ChannelNotesStripper
{
    /**
     * Drop everything from the `## Channel notes` heading onward, plus the
     * `---` separator that typically precedes it.
     */
    public function strip(string $body): string
    {
        $stripped = preg_replace(
            '/\n(?:---\s*\n)?##\s+Channel\s+notes\b.*$/is',
            "\n",
            $body
        );

        return $stripped ?? $body;
    }
}
