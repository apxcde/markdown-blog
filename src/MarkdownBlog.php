<?php

namespace apxcde\MarkdownBlog;

use Illuminate\Support\Collection;

class MarkdownBlog
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
    ) {}

    public function all(): Collection
    {
        return $this->articleRepository->all();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->articleRepository->findBySlug($slug);
    }

    public function repository(): ArticleRepository
    {
        return $this->articleRepository;
    }
}
