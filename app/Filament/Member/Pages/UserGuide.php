<?php

namespace App\Filament\Member\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserGuide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.member.pages.user-guide';

    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public ?string $chapter = '01-bienvenida';

    public bool $showSidebarOnMobile = false;

    public static function getNavigationLabel(): string
    {
        return __('Manual de Usuario');
    }

    public function getTitle(): string
    {
        return __('Manual de Usuario');
    }

    public function mount(): void
    {
        // Fallback to default if empty or invalid
        if (empty($this->chapter) || !file_exists(base_path("docs/guides/user/{$this->chapter}.md"))) {
            $this->chapter = '01-bienvenida';
            $this->showSidebarOnMobile = true;
        }
    }

    /**
     * Get list of chapters
     */
    public function getChapters(): array
    {
        return $this->getChaptersList();
    }

    /**
     * Load active chapter content and convert to HTML
     */
    public function getActiveContent(): string
    {
        $slug = basename($this->chapter);
        $path = base_path("docs/guides/user/{$slug}.md");

        if (!file_exists($path)) {
            $path = base_path("docs/guides/user/01-bienvenida.md");
        }

        $rawContent = file_get_contents($path);

        // Rewrite relative image paths to the public symlink path
        $rawContent = str_replace('../screenshots/', '/screenshots/', $rawContent);

        // Convert markdown to HTML (using Laravel 11's built-in parser)
        return Str::markdown($rawContent);
    }

    /**
     * Get next chapter for bottom pagination
     */
    public function getNextChapter(): ?array
    {
        $chapters = $this->getChaptersList();
        $currentIndex = $this->getCurrentIndex($chapters);

        if ($currentIndex !== null && isset($chapters[$currentIndex + 1])) {
            return $chapters[$currentIndex + 1];
        }

        return null;
    }

    /**
     * Get previous chapter for bottom pagination
     */
    public function getPreviousChapter(): ?array
    {
        $chapters = $this->getChaptersList();
        $currentIndex = $this->getCurrentIndex($chapters);

        if ($currentIndex !== null && isset($chapters[$currentIndex - 1])) {
            return $chapters[$currentIndex - 1];
        }

        return null;
    }

    /**
     * Navigate to a specific chapter
     */
    public function selectChapter(string $slug): void
    {
        $this->chapter = $slug;
        $this->showSidebarOnMobile = false;
    }

    /**
     * Force showing the sidebar (index) on mobile
     */
    public function showMobileMenu(): void
    {
        $this->showSidebarOnMobile = true;
    }

    /**
     * Base list of chapters (ordered and unfiltered)
     */
    private function getChaptersList(): array
    {
        $files = glob(base_path('docs/guides/user/*.md'));
        natsort($files);
        
        $chapters = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (str_starts_with($filename, '_')) {
                continue;
            }

            $slug = pathinfo($filename, PATHINFO_FILENAME);
            $content = file_get_contents($file);
            $title = '';

            // Extract the chapter title from the H1 header
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            } else {
                $title = ucwords(str_replace('-', ' ', $slug));
            }

            $chapters[] = [
                'slug' => $slug,
                'title' => $title,
            ];
        }

        return $chapters;
    }

    private function getCurrentIndex(array $chapters): ?int
    {
        foreach ($chapters as $index => $ch) {
            if ($ch['slug'] === $this->chapter) {
                return $index;
            }
        }

        return null;
    }
}
