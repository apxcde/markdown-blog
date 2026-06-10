<?php

namespace xsavo\MarkdownBlog\Support;

/**
 * Minimal frontmatter parser supporting a small, deliberate subset of YAML.
 *
 * This is intentionally NOT a full YAML parser. Supported syntax inside the
 * leading `---` ... `---` block is limited to scalar `key: value` pairs, one
 * per line:
 *
 * - values may be unquoted, single-quoted, or double-quoted (surrounding
 *   quotes are stripped; no escape-sequence processing is performed)
 * - every value is returned as a string ('true', '42', etc. are not cast)
 * - blank lines and lines starting with '#' are skipped
 *
 * Everything else is silently ignored, including:
 *
 * - lists / sequences (`- item` lines, or inline `[a, b]` kept as a literal string)
 * - nested maps and indentation-based structure
 * - multiline scalars (block `|` / folded `>` styles)
 * - anchors, aliases, tags, and multi-document markers
 * - lines without a colon
 *
 * If richer metadata is needed, keep frontmatter flat and scalar, or parse
 * the value (e.g. a comma-separated list) in the consumer.
 */
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
