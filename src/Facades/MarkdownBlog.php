<?php

namespace xsavo\MarkdownBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \xsavo\MarkdownBlog\MarkdownBlog
 *
 * @method static \Illuminate\Support\Collection all()
 * @method static array|null findBySlug(string $slug)
 * @method static \xsavo\MarkdownBlog\ArticleRepository repository()
 */
class MarkdownBlog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \xsavo\MarkdownBlog\MarkdownBlog::class;
    }
}
