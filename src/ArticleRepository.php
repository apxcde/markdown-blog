<?php

namespace apxcde\MarkdownBlog;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use apxcde\MarkdownBlog\Support\FrontmatterParser;

class ArticleRepository
{
    public function __construct(
        private readonly FrontmatterParser $frontmatterParser,
        private readonly string $articlesPath,
        private readonly string $articleFilename = 'page.md',
        private readonly int $excerptLength = 220,
        private readonly string $dateFormat = 'M j, Y',
    ) {}

    public function all(): Collection
    {
        $articles = $this->scan();

        if ($this->draftsAreVisible()) {
            return $articles;
        }

        return $articles->where('status', '!=', 'draft')->values();
    }

    public function current(): Collection
    {
        return $this->all()->where('status', 'current')->values();
    }

    public function archived(): Collection
    {
        return $this->all()->where('status', 'archive')->values();
    }

    public function drafts(): Collection
    {
        if (! $this->draftsAreVisible()) {
            return collect();
        }

        return $this->scan()->where('status', 'draft')->values();
    }

    public function listed(): Collection
    {
        return $this->all()->where('status', '!=', 'archive')->values();
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', Str::slug($slug));
    }

    /**
     * @throws FileNotFoundException
     */
    private function hydrate(string $path): ?array
    {
        [$frontmatter, $content] = $this->frontmatterParser->parse(File::get($path));

        $slug = Str::slug($this->asString($frontmatter['slug'] ?? basename(dirname($path))));

        if ($slug === '') {
            return null;
        }

        $date = $this->asString($frontmatter['date'] ?? '');

        return [
            'slug' => $slug,
            'title' => $this->asString($frontmatter['title'] ?? Str::headline($slug)),
            'description' => $this->asString($frontmatter['description'] ?? $this->excerpt($content)),
            'author' => $this->asString($frontmatter['author'] ?? ''),
            'date' => $date,
            'formatted_date' => $this->formatDate($date),
            'status' => $this->normalizeStatus($this->asString($frontmatter['status'] ?? '')),
            'content' => $content,
        ];
    }

    private function excerpt(string $markdown): string
    {
        $text = strip_tags(Str::markdown($markdown));
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        return Str::limit($text, $this->excerptLength);
    }

    private function formatDate(string $date): ?string
    {
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->format($this->dateFormat);
        } catch (\Throwable) {
            return $date;
        }
    }

    private function compareArticleDates(array $left, array $right): int
    {
        return $this->sortableDateValue($right['date'] ?? '') <=> $this->sortableDateValue($left['date'] ?? '');
    }

    private function sortableDateValue(string $date): int
    {
        if ($date === '') {
            return PHP_INT_MIN;
        }

        try {
            return Carbon::parse($date)->getTimestamp();
        } catch (\Throwable) {
            return PHP_INT_MIN;
        }
    }

    private function scan(): Collection
    {
        if (! File::isDirectory($this->articlesPath)) {
            return collect();
        }

        return collect(File::allFiles($this->articlesPath))
            ->filter(fn ($file) => $file->getFilename() === $this->articleFilename)
            ->map(fn ($file) => $this->hydrate($file->getPathname()))
            ->filter()
            ->sort(fn (array $left, array $right) => $this->compareArticleDates($left, $right))
            ->values();
    }

    private function draftsAreVisible(): bool
    {
        return app()->environment('local');
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'archive' => 'archive',
            'draft' => 'draft',
            default => 'current',
        };
    }

    private function asString(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}
