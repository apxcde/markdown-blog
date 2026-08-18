# apxcde/markdown-blog

[![Latest Version on Packagist](https://img.shields.io/packagist/v/apxcde/markdown-blog.svg)](https://packagist.org/packages/apxcde/markdown-blog)
[![Tests](https://github.com/apxcde/markdown-blog/actions/workflows/tests.yml/badge.svg)](https://github.com/apxcde/markdown-blog/actions/workflows/tests.yml)
[![License](https://img.shields.io/packagist/l/apxcde/markdown-blog.svg)](LICENSE.md)

A Laravel package that turns a directory of markdown files into blog articles.

It gives you:

- recursive discovery of article files under a configured directory
- frontmatter parsing for common article metadata
- normalized article payloads as plain arrays
- a generated excerpt when an article declares no description
- display-ready date formatting
- slug-based article lookup, newest first

It deliberately stops there: routes, controllers, Livewire components, and views
stay in your application.

## Requirements

- PHP 8.3+
- Laravel 13+

## Installation

```bash
composer require apxcde/markdown-blog
```

The service provider and the `MarkdownBlog` facade alias are registered
automatically through Laravel package discovery.

## Configuration

Publish the config file to change where articles live or how they are formatted:

```bash
php artisan vendor:publish --tag=markdown-blog-config
```

```php
// config/markdown-blog.php
return [
    'articles_path' => resource_path('markdown/articles'),
    'article_filename' => 'page.md',
    'excerpt_length' => 220,
    'date_format' => 'M j, Y',
];
```

| Key | Purpose |
| --- | --- |
| `articles_path` | Directory scanned for articles. A missing directory yields an empty collection rather than an error. |
| `article_filename` | Only files with this exact name are treated as articles. |
| `excerpt_length` | Truncation length for the excerpt generated when `description` is absent. An ellipsis is appended, so the result runs a few characters longer. |
| `date_format` | PHP date format applied to `formatted_date`. |

## Article structure

Each article is a directory containing one `page.md`. The directory is scanned
recursively, so you are free to group articles into subdirectories.

```text
resources/
└── markdown/
    └── articles/
        ├── first-post/
        │   └── page.md
        └── 2024/
            └── another-post/
                └── page.md
```

```md
---
title: "Infinite Scroll with Laravel and Livewire"
description: "Infinite scrolling is a popular feature for content-heavy pages."
author: "Rick Mwamodo"
date: "2024-01-17"
slug: "infinite-scroll-with-laravel-and-livewire"
---

Article body goes here.
```

### Frontmatter fields

Every field is optional. When one is **absent**, the package falls back as
follows. Note that a key which is present but blank (`title:`) counts as a
value — you get an empty string, not the fallback.

| Field | Fallback |
| --- | --- |
| `slug` | The article's parent directory name. Either way the value is passed through `Str::slug()`. An article that resolves to an empty slug is skipped. |
| `title` | `Str::headline()` of the slug, so `first-post` becomes `First Post`. |
| `description` | An excerpt built from the body: markdown rendered, tags stripped, whitespace collapsed, truncated to `excerpt_length`. |
| `author` | An empty string. |
| `date` | An empty string, and `formatted_date` is then `null`. |

### Frontmatter syntax

This is **not** a YAML parser. It handles a small, deliberate subset: flat
`key: value` pairs, one per line. Values may be unquoted, single-quoted, or
double-quoted; surrounding quotes are stripped and every value is returned as a
string, so `42` and `true` are not cast. Blank lines, `#` comments, and lines
with no colon are skipped.

Richer YAML is not rejected — it is quietly mis-read, so avoid it:

| You write | You get |
| --- | --- |
| `author:` then an indented `name: Rick` | Indentation is ignored, so `name` is hoisted to a top-level key and `author` becomes `''`. |
| `tags:` then `- php`, `- laravel` | The `- ` lines have no colon and are dropped; `tags` becomes `''`. |
| `items:` then `- name: foo` | That line *does* contain a colon, so you get a literal key `- name`. |
| `tags: [php, laravel]` | Kept verbatim as the string `'[php, laravel]'`. |

Keep frontmatter flat and scalar, and parse richer values (a comma-separated
list, for example) in your own code.

## Usage

### Facade

```php
use apxcde\MarkdownBlog\Facades\MarkdownBlog;

$articles = MarkdownBlog::all();
$article = MarkdownBlog::findBySlug('infinite-scroll-with-laravel-and-livewire');
```

### Resolved service

```php
use apxcde\MarkdownBlog\MarkdownBlog;

$blog = app(MarkdownBlog::class);

$articles = $blog->all();
$article = $blog->findBySlug('infinite-scroll-with-laravel-and-livewire');
$repository = $blog->repository();
```

### Repository

```php
use apxcde\MarkdownBlog\ArticleRepository;

$repository = app(ArticleRepository::class);

$articles = $repository->all();
$article = $repository->findBySlug('infinite-scroll-with-laravel-and-livewire');
```

### In a controller

```php
use apxcde\MarkdownBlog\Facades\MarkdownBlog;

Route::get('/blog', fn () => view('blog.index', [
    'articles' => MarkdownBlog::all(),
]));

Route::get('/blog/{slug}', function (string $slug) {
    abort_if(! $article = MarkdownBlog::findBySlug($slug), 404);

    return view('blog.show', ['article' => $article]);
});
```

## API

### `all(): Illuminate\Support\Collection`

Returns every article as an array, sorted by `date` descending — newest first.
Articles with no date, or with a date Carbon cannot parse, sort last. If
`articles_path` does not exist, you get an empty collection.

### `findBySlug(string $slug): ?array`

Returns the matching article, or `null`. The argument is run through
`Str::slug()` first, so `Infinite Scroll` and `infinite-scroll` both match.

## Returned article shape

```php
[
    'slug' => 'infinite-scroll-with-laravel-and-livewire',
    'title' => 'Infinite Scroll with Laravel and Livewire',
    'description' => 'Infinite scrolling is a popular feature for content-heavy pages.',
    'author' => 'Rick Mwamodo',
    'date' => '2024-01-17',
    'formatted_date' => 'Jan 17, 2024',
    'content' => 'Article body goes here.',
]
```

`date` is returned exactly as written in the frontmatter. `formatted_date` is
that date parsed by Carbon and rendered with `date_format`; it is `null` when no
date is set, and falls back to the raw string when the date cannot be parsed.
`content` is the markdown body with the frontmatter block removed, trimmed, and
left unrendered — render it in your view.

## Testing

```bash
composer test
```

## Contributing

This package is developed inside ApexCode's Turbine platform monorepo and
published here as a one-way mirror. Please open **issues** on this repository.

Pull requests are welcome, but note that this repository is machine-managed:
its branches are overwritten by the next sync from the monorepo, so a PR cannot
simply be merged here. Accepted changes are ported upstream and land back
through a later sync, with credit preserved.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
