<?php

return [
    'articles_path' => resource_path('markdown/articles'),
    'article_filename' => 'page.md',
    'excerpt_length' => 220,
    'date_format' => 'M j, Y',

    /*
     * Visibility of articles whose frontmatter declares `status: draft`.
     * null (default) shows drafts only when the application environment is
     * "local"; true or false forces them on or off in every environment.
     */
    'show_drafts' => env('MARKDOWN_BLOG_SHOW_DRAFTS'),
];
