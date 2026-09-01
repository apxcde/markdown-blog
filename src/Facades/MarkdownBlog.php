<?php

namespace apxcde\MarkdownBlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \apxcde\MarkdownBlog\MarkdownBlog
 *
 * @method static \Illuminate\Support\Collection all()
 * @method static \Illuminate\Support\Collection current()
 * @method static \Illuminate\Support\Collection archived()
 * @method static \Illuminate\Support\Collection drafts()
 * @method static \Illuminate\Support\Collection listed()
 * @method static array|null findBySlug(string $slug)
 * @method static \apxcde\MarkdownBlog\ArticleRepository repository()
 */
class MarkdownBlog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \apxcde\MarkdownBlog\MarkdownBlog::class;
    }
}
