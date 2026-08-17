<?php

use apxcde\MarkdownBlog\Support\FrontmatterParser;

it('parses frontmatter from utf8 bom-prefixed markdown', function () {
    $raw = "\xEF\xBB\xBF---\n"
        ."title: BOM Title\n"
        ."slug: bom-title\n"
        ."date: 2024-01-17\n"
        ."---\n"
        ."Body content\n";

    [$frontmatter, $content] = app(FrontmatterParser::class)->parse($raw);

    expect($frontmatter)->toMatchArray([
        'title' => 'BOM Title',
        'slug' => 'bom-title',
        'date' => '2024-01-17',
    ])->and($content)->toBe('Body content');
});
