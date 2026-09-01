<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use apxcde\MarkdownBlog\ArticleRepository;
use apxcde\MarkdownBlog\MarkdownBlog;

beforeEach(function () {
    config()->set('markdown-blog.articles_path', __DIR__.'/Fixtures/articles');
});

it('lists markdown articles sorted by date descending', function () {
    $articles = app(ArticleRepository::class)->all();

    expect($articles->pluck('slug')->all())->toBe([
        'archived-post',
        'custom-newest',
        'human-date',
        'no-description',
        'older-post',
        'unknown-status',
    ])->and($articles->first())->toMatchArray([
        'slug' => 'archived-post',
        'title' => 'Archived Article',
        'description' => 'Archived description.',
        'author' => 'Rick Mwamodo',
        'date' => '2024-02-01',
        'formatted_date' => 'Feb 1, 2024',
        'status' => 'archive',
    ]);
});

it('defaults missing or unknown status to current and lists current before archive filters', function () {
    $repository = app(ArticleRepository::class);

    expect($repository->current()->pluck('slug')->all())->toBe([
        'custom-newest',
        'human-date',
        'no-description',
        'older-post',
        'unknown-status',
    ])->and($repository->archived()->pluck('slug')->all())->toBe([
        'archived-post',
    ])->and($repository->listed()->pluck('slug')->all())->toBe([
        'custom-newest',
        'human-date',
        'no-description',
        'older-post',
        'unknown-status',
    ])->and($repository->findBySlug('no-description')['status'])->toBe('current')
        ->and($repository->findBySlug('unknown-status')['status'])->toBe('current')
        ->and($repository->findBySlug('archived-post')['status'])->toBe('archive')
        ->and($repository->findBySlug('draft-post'))->toBeNull()
        ->and($repository->drafts())->toHaveCount(0)
        ->and($repository->all()->pluck('slug')->all())->not->toContain('draft-post');
});

it('exposes current and archived collections through the package service', function () {
    $blog = app(MarkdownBlog::class);

    expect($blog->current()->pluck('status')->unique()->all())->toBe(['current'])
        ->and($blog->archived()->pluck('status')->unique()->all())->toBe(['archive'])
        ->and($blog->drafts())->toHaveCount(0);
});

it('exposes drafts only when the application environment is local', function () {
    $this->app['env'] = 'local';

    $blog = app(MarkdownBlog::class);

    expect($blog->drafts()->pluck('slug')->all())->toBe(['draft-post'])
        ->and($blog->all()->pluck('slug')->all())->toBe([
            'draft-post',
            'archived-post',
            'custom-newest',
            'human-date',
            'no-description',
            'older-post',
            'unknown-status',
        ])->and($blog->listed()->pluck('slug')->all())->toBe([
            'draft-post',
            'custom-newest',
            'human-date',
            'no-description',
            'older-post',
            'unknown-status',
        ])->and($blog->current()->pluck('slug')->all())->not->toContain('draft-post')
        ->and($blog->archived()->pluck('slug')->all())->toBe(['archived-post'])
        ->and($blog->findBySlug('draft-post'))->toMatchArray([
            'slug' => 'draft-post',
            'status' => 'draft',
            'title' => 'Draft Article',
        ]);
});

it('shows drafts in any environment when show_drafts is true', function () {
    config()->set('markdown-blog.show_drafts', true);

    $repository = app(ArticleRepository::class);

    expect(app()->environment())->not->toBe('local')
        ->and($repository->drafts()->pluck('slug')->all())->toBe(['draft-post'])
        ->and($repository->listed()->first()['slug'])->toBe('draft-post')
        ->and($repository->current()->pluck('slug')->all())->not->toContain('draft-post')
        ->and($repository->findBySlug('draft-post'))->not->toBeNull();
});

it('hides drafts in the local environment when show_drafts is false', function () {
    $this->app['env'] = 'local';
    config()->set('markdown-blog.show_drafts', false);

    $repository = app(ArticleRepository::class);

    expect($repository->drafts())->toHaveCount(0)
        ->and($repository->all()->pluck('slug')->all())->not->toContain('draft-post')
        ->and($repository->findBySlug('draft-post'))->toBeNull();
});

it('scans the articles directory once per repository instance', function () {
    $path = sys_get_temp_dir().'/markdown-blog-'.Str::random(8);
    File::copyDirectory(__DIR__.'/Fixtures/articles', $path);
    config()->set('markdown-blog.articles_path', $path);

    try {
        $repository = app(ArticleRepository::class);
        $before = $repository->all()->count();

        File::ensureDirectoryExists($path.'/late-post');
        File::put($path.'/late-post/page.md', "---\ntitle: Late Post\ndate: 2024-05-01\n---\n\nLate body.");

        expect($repository->all())->toHaveCount($before)
            ->and($repository->findBySlug('late-post'))->toBeNull()
            ->and(app(ArticleRepository::class)->all())->toHaveCount($before + 1);
    } finally {
        File::deleteDirectory($path);
    }
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
