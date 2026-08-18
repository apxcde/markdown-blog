<?php

namespace apxcde\MarkdownBlog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use apxcde\MarkdownBlog\Support\FrontmatterParser;

class MarkdownBlogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('markdown-blog')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(FrontmatterParser::class);

        $this->app->bind(ArticleRepository::class, function ($app): ArticleRepository {
            return new ArticleRepository(
                frontmatterParser: $app->make(FrontmatterParser::class),
                articlesPath: (string) $app['config']->get('markdown-blog.articles_path', resource_path('markdown/articles')),
                articleFilename: (string) $app['config']->get('markdown-blog.article_filename', 'page.md'),
                excerptLength: (int) $app['config']->get('markdown-blog.excerpt_length', 220),
                dateFormat: (string) $app['config']->get('markdown-blog.date_format', 'M j, Y'),
            );
        });

        $this->app->bind(MarkdownBlog::class, function ($app): MarkdownBlog {
            return new MarkdownBlog($app->make(ArticleRepository::class));
        });
    }
}
