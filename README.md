# xsavo Markdown Blog

`xsavo/markdown-blog` extracts the markdown article-loading logic currently used in `internal/apexcode` into a reusable Turbine platform package.

It currently provides:

- discovery of markdown articles from a configured directory
- simple frontmatter parsing for `title`, `description`, `author`, `date`, and `slug`
- normalized article payloads with computed excerpts and formatted dates
- slug-based article lookup through a repository or facade-backed service

## Current Status

This package currently covers the content layer only.

What exists today:

- `xsavo\MarkdownBlog\ArticleRepository` loads `page.md` files from the configured articles path
- `xsavo\MarkdownBlog\MarkdownBlog` exposes `all()` and `findBySlug()` for application use
- package tests cover article discovery, fallback metadata, and slug lookup

What does not exist yet:

- routes
- controllers
- Livewire components
- Blade blog index/detail views

Those remain host-application responsibilities until the platform blog UI contract is defined.

## Installation

From a product or internal app directory, add the local path repository and require the package:

```bash
composer config repositories.markdown-blog path ../../platform/markdown-blog
composer require xsavo/markdown-blog:*@dev
```

The service provider `xsavo\MarkdownBlog\MarkdownBlogServiceProvider` is auto-registered through Laravel package discovery.

## Usage

Resolve the repository directly:

```php
use xsavo\MarkdownBlog\ArticleRepository;

$articles = app(ArticleRepository::class)->all();
$article = app(ArticleRepository::class)->findBySlug('infinite-scroll-with-laravel-and-livewire');
```

Or use the package service:

```php
use xsavo\MarkdownBlog\MarkdownBlog;

$articles = app(MarkdownBlog::class)->all();
$article = app(MarkdownBlog::class)->findBySlug('infinite-scroll-with-laravel-and-livewire');
```

Each article is returned as an array with this shape:

```php
[
    'slug' => 'infinite-scroll-with-laravel-and-livewire',
    'title' => 'Infinite Scroll with Laravel and Livewire',
    'description' => 'Infinite scrolling is a popular feature...',
    'author' => 'Rick Mwamodo',
    'date' => '2024-01-17',
    'formatted_date' => 'Jan 17, 2024',
    'content' => '# Markdown body...',
]
```

## Configuration

The package ships with `config/markdown-blog.php`:

```php
return [
    'articles_path' => resource_path('markdown/articles'),
    'article_filename' => 'page.md',
    'excerpt_length' => 220,
    'date_format' => 'M j, Y',
];
```

## Testing

```bash
composer test
```
