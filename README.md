# xsavo/markdown-blog

`xsavo/markdown-blog` is a Laravel package for loading markdown-based blog articles from your application files.

It provides:

- discovery of article files from a configured directory
- frontmatter parsing for common article metadata
- normalized article payloads
- generated excerpts from markdown content
- date formatting for display
- slug-based article lookup

## Requirements

- PHP 8.3+
- Laravel 13+

## Installation

Require the package with Composer:

```bash
composer require xsavo/markdown-blog
```

The package service provider is auto-registered through Laravel package discovery.

## Configuration

Publish the config file if you want to customize the article location or formatting:

```bash
php artisan vendor:publish --tag=markdown-blog-config
```

Default configuration:

```php
return [
    'articles_path' => resource_path('markdown/articles'),
    'article_filename' => 'page.md',
    'excerpt_length' => 220,
    'date_format' => 'M j, Y',
];
```

## Article structure

By default, the package looks for files named `page.md` inside the configured articles directory.

Example:

```text
resources/
└── markdown/
    └── articles/
        ├── first-post/
        │   └── page.md
        └── another-post/
            └── page.md
```

Example article:

```md
---
title: Infinite Scroll with Laravel and Livewire
description: Infinite scrolling is a popular feature for content-heavy pages.
author: Rick Mwamodo
date: 2024-01-17
slug: infinite-scroll-with-laravel-and-livewire
---

# Infinite Scroll with Laravel and Livewire

Article body goes here.
```

Supported frontmatter fields:

- `title`
- `description`
- `author`
- `date`
- `slug`

If some fields are omitted, the package falls back to sensible defaults where possible.

## Usage

### Resolve the repository

```php
use xsavo\MarkdownBlog\ArticleRepository;

$repository = app(ArticleRepository::class);

$articles = $repository->all();
$article = $repository->findBySlug('infinite-scroll-with-laravel-and-livewire');
```

### Resolve the package service

```php
use xsavo\MarkdownBlog\MarkdownBlog;

$blog = app(MarkdownBlog::class);

$articles = $blog->all();
$article = $blog->findBySlug('infinite-scroll-with-laravel-and-livewire');
```

### Use the facade

```php
use xsavo\MarkdownBlog\Facades\MarkdownBlog;

$articles = MarkdownBlog::all();
$article = MarkdownBlog::findBySlug('infinite-scroll-with-laravel-and-livewire');
```

## Returned article shape

Each article is returned as an array like:

```php
[
    'slug' => 'infinite-scroll-with-laravel-and-livewire',
    'title' => 'Infinite Scroll with Laravel and Livewire',
    'description' => 'Infinite scrolling is a popular feature for content-heavy pages.',
    'author' => 'Rick Mwamodo',
    'date' => '2024-01-17',
    'formatted_date' => 'Jan 17, 2024',
    'content' => '# Infinite Scroll with Laravel and Livewire...',
]
```

## Notes

This package handles article loading and normalization only. Rendering routes, controllers, Livewire components, and views remain the responsibility of the host application.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
