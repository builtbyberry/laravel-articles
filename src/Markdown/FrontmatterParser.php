<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

use Symfony\Component\Yaml\Yaml;

class FrontmatterParser
{
    /**
     * Split a markdown file into [frontmatter array, body string].
     *
     * If there is no frontmatter block at the top, returns [[], $raw].
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    public function parse(string $raw): array
    {
        if (! preg_match('/^---\r?\n(.*?)\r?\n---\r?\n(.*)$/s', $raw, $matches)) {
            return [[], $raw];
        }

        try {
            $meta = Yaml::parse($matches[1]) ?? [];
            if (! is_array($meta)) {
                $meta = [];
            }
        } catch (\Throwable) {
            $meta = [];
        }

        return [$meta, $matches[2]];
    }
}
