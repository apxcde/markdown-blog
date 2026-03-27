<?php

namespace xsavo\MarkdownBlog\Support;

class FrontmatterParser
{
    private const UTF8_BOM = "\xEF\xBB\xBF";

    public function parse(string $raw): array
    {
        $raw = $this->stripUtf8Bom($raw);

        if (! preg_match('/\A---\R(.*?)\R---\R?(.*)\z/s', $raw, $matches)) {
            return [[], trim($raw)];
        }

        return [$this->parseBlock($matches[1]), trim($matches[2])];
    }

    private function parseBlock(string $frontmatter): array
    {
        $parsed = [];

        foreach (preg_split('/\R/', $frontmatter) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);

            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $parsed[$key] = $this->parseValue($value);
        }

        return $parsed;
    }

    private function parseValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $isDoubleQuoted = str_starts_with($value, '"') && str_ends_with($value, '"');
        $isSingleQuoted = str_starts_with($value, "'") && str_ends_with($value, "'");

        if ($isDoubleQuoted || $isSingleQuoted) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    private function stripUtf8Bom(string $raw): string
    {
        if (! str_starts_with($raw, self::UTF8_BOM)) {
            return $raw;
        }

        return substr($raw, strlen(self::UTF8_BOM));
    }
}
