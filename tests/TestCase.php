<?php

namespace apxcde\MarkdownBlog\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use apxcde\MarkdownBlog\MarkdownBlogServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MarkdownBlogServiceProvider::class,
        ];
    }
}
