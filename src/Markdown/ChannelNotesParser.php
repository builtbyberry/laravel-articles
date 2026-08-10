<?php

namespace BuiltByBerry\LaravelArticles\Markdown;

use Illuminate\Support\Str;

class ChannelNotesParser
{
    /**
     * @return array<string, array{key: string, heading: string, markdown: string}>
     */
    public function extract(string $body): array
    {
        $sectionOffset = $this->sectionOffset($body, ['Channel notes']);

        if ($sectionOffset === null) {
            return [];
        }

        $section = substr($body, $sectionOffset);
        $notes = [];
        $currentKey = null;

        foreach ($this->scan($section) as $index => $scannedLine) {
            if ($index === 0) {
                continue;
            }

            $line = $scannedLine['line'];
            $headingLine = rtrim($line, "\r\n");

            if ($scannedLine['structural'] && preg_match('/^##(?!#)\s+/', $headingLine) === 1) {
                break;
            }

            if ($scannedLine['structural'] && preg_match('/^###(?!#)\s+(.+?)\s*#*\s*$/', $headingLine, $matches) === 1) {
                $heading = trim($matches[1]);
                $key = Str::slug($heading);

                if ($key === '' || array_key_exists($key, $notes)) {
                    $currentKey = null;

                    continue;
                }

                $notes[$key] = [
                    'key' => $key,
                    'heading' => $heading,
                    'markdown' => '',
                ];
                $currentKey = $key;

                continue;
            }

            if ($scannedLine['structural'] && preg_match('/^###(?!#)\s*#*\s*$/', $headingLine) === 1) {
                $currentKey = null;

                continue;
            }

            if ($currentKey !== null) {
                $notes[$currentKey]['markdown'] .= $line;
            }
        }

        return array_map(function (array $note): array {
            $note['markdown'] = trim($note['markdown'], "\r\n");

            return $note;
        }, $notes);
    }

    /**
     * @param  list<string>  $headings
     *
     * @internal Used by ChannelNotesStripper to share the same fence-aware scan.
     */
    public function sectionOffset(string $body, array $headings): ?int
    {
        if ($headings === []) {
            return null;
        }

        foreach ($this->scan($body) as $scannedLine) {
            $headingLine = rtrim($scannedLine['line'], "\r\n");

            if ($scannedLine['structural'] && preg_match('/^##(?!#)\s+(.+?)\s*#*\s*$/', $headingLine, $matches) === 1) {
                foreach ($headings as $heading) {
                    if ($this->headingMatches($matches[1], $heading)) {
                        return $scannedLine['offset'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return \Generator<int, array{line: string, structural: bool, offset: int}>
     */
    private function scan(string $body): \Generator
    {
        $lines = preg_split('/(?<=\n)/', $body) ?: [$body];
        $offset = 0;
        $inFence = false;
        $fenceCharacter = null;
        $fenceLength = 0;

        foreach ($lines as $line) {
            $headingLine = rtrim($line, "\r\n");
            $isFenceContent = $inFence;

            if ($inFence && $this->isClosingFence($headingLine, $fenceCharacter, $fenceLength)) {
                $inFence = false;
                $fenceCharacter = null;
                $fenceLength = 0;
            } elseif (! $inFence && ($fence = $this->openingFence($headingLine)) !== null) {
                [$fenceCharacter, $fenceLength] = $fence;
                $inFence = true;
                $isFenceContent = true;
            }

            yield [
                'line' => $line,
                'structural' => ! $isFenceContent,
                'offset' => $offset,
            ];

            $offset += strlen($line);
        }
    }

    /**
     * @return array{string, int}|null
     */
    private function openingFence(string $line): ?array
    {
        if (preg_match('/^ {0,3}(`{3,}|~{3,})/', $line, $matches) !== 1) {
            return null;
        }

        return [$matches[1][0], strlen($matches[1])];
    }

    private function isClosingFence(string $line, ?string $character, int $minimumLength): bool
    {
        if ($character === null) {
            return false;
        }

        $quotedCharacter = preg_quote($character, '/');

        return preg_match('/^ {0,3}'.$quotedCharacter.'{'.$minimumLength.',}[ \t]*$/', $line) === 1;
    }

    private function headingMatches(string $candidate, string $heading): bool
    {
        $words = array_map(
            fn (string $word): string => preg_quote($word, '/'),
            preg_split('/\s+/', trim($heading)) ?: [],
        );

        if ($words === []) {
            return false;
        }

        return preg_match('/^'.implode('\s+', $words).'\b/i', trim($candidate)) === 1;
    }
}
