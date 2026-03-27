<?php

namespace xsavo\MarkdownBlog\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use xsavo\MarkdownBlog\MarkdownBlogServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MarkdownBlogServiceProvider::class,
        ];
    }
}
