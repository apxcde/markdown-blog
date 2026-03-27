<?php

use xsavo\MarkdownBlog\ArticleRepository;
use xsavo\MarkdownBlog\MarkdownBlog;

beforeEach(function () {
    config()->set('markdown-blog.articles_path', __DIR__.'/Fixtures/articles');
});

it('lists markdown articles sorted by date descending', function () {
    $articles = app(ArticleRepository::class)->all();

    expect($articles->pluck('slug')->all())->toBe([
        'custom-newest',
        'human-date',
        'no-description',
        'older-post',
    ])->and($articles->first())->toMatchArray([
        'slug' => 'custom-newest',
        'title' => 'Newest Article',
        'description' => 'Newest description.',
        'author' => 'Rick Mwamodo',
        'date' => '2024-01-17',
        'formatted_date' => 'Jan 17, 2024',
    ]);
});

it('sorts Carbon-parseable non-iso dates correctly', function () {
    $articles = app(ArticleRepository::class)->all()->keyBy('slug');

    expect($articles['human-date']['formatted_date'])->toBe('Jan 2, 2024');
});

it('builds fallback values when frontmatter is missing optional fields', function () {
    $article = app(ArticleRepository::class)->findBySlug('no-description');

    expect($article)->not->toBeNull()
        ->and($article['slug'])->toBe('no-description')
        ->and($article['title'])->toBe('Fallback Description')
        ->and($article['description'])->toStartWith('This article does not declare a description')
        ->and($article['formatted_date'])->toBe('Jan 1, 2024');
});

it('finds an article by slug through the package service', function () {
    $article = app(MarkdownBlog::class)->findBySlug('custom-newest');

    expect($article)->not->toBeNull()
        ->and($article['title'])->toBe('Newest Article');
});

it('returns null for an unknown slug', function () {
    expect(app(ArticleRepository::class)->findBySlug('missing-article'))->toBeNull();
});

it('returns an empty collection when the configured articles path is missing', function () {
    config()->set('markdown-blog.articles_path', __DIR__.'/Fixtures/missing');

    expect(app(ArticleRepository::class)->all())->toHaveCount(0);
});
