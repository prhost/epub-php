<?php

declare(strict_types=1);

namespace Prhost\Epub3;

use Prhost\Epub3\Elements\ManifestItem;
use Prhost\Epub3\Files\Chapter;
use Prhost\Epub3\Helpers\Str;
use Prhost\Epub3\Makers\ChapterMaker;
use Prhost\Epub3\Makers\ContainerMaker;
use Prhost\Epub3\Makers\CoverMaker;
use Prhost\Epub3\Makers\NavegationMaker;
use Prhost\Epub3\Makers\PackageMaker;

class Epub
{
    protected const MIME_TYPE = 'application/epub+zip';

    protected string $title;

    /**
     * @var NavegationMaker
     */
    protected $navegation;

    /**
     * @var CoverMaker
     */
    protected $coverMaker;

    /**
     * @var PackageMaker
     */
    protected $packageMaker;

    /**
     * @var Chapter[]
     */
    protected array $chaptersFiles = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function nav(string $basePath = null, string $filename = null): NavegationMaker
    {
        if (null === $this->navegation) {
            $this->navegation = new NavegationMaker($this->getTitle(), $filename, $basePath);
        }

        return $this->navegation;
    }

    /**
     * @param string $filename
     * @param string $basePath
     * @return CoverMaker
     */
    public function cover(string $basePath = 'EPUB/xhtml', string $filename = 'cover.xhtml'): CoverMaker
    {
        if (null === $this->coverMaker) {
            $this->coverMaker = new CoverMaker($this->getTitle(), $filename, $basePath);
        }

        return $this->coverMaker;
    }

    public function addChapter(string $title, string $content = null, string $filename = null, string $basePath = null): self
    {
        $chapter = new ChapterMaker($title, $content, $filename, $basePath);
        $this->appendChapter($chapter);

        return $this;
    }

    public function appendChapter(ChapterMaker $chapterMaker): self
    {
        $this->chaptersFiles[] = $chapterMaker->makeFile();

        return $this;
    }

    /**
     * @return Chapter[]
     */
    public function getChaptersFiles(): array
    {
        return $this->chaptersFiles;
    }

    protected function generateEpub(): void
    {
        $this->generateMimeType();
        $this->generateContainer();
    }

    protected function getPackageMaker(): PackageMaker
    {
        if (null === $this->packageMaker) {
            $this->packageMaker = new PackageMaker($this->getTitle(), null, 'EPUB');

            $coverFile = $this->coverMaker->makeFile();

            $this->packageMaker->appendManifestItem(
                ManifestItem::fromFile(
                    $coverFile,
                    'EPUB'
                )
            );

            $this->packageMaker->createMetadataItem('meta', null, [
                'name' => 'cover',
                'contet' => $coverFile->getFilename(),
            ]);

            foreach ($this->chaptersFiles as $chapterFile) {
                $manifestItem = ManifestItem::fromFile($chapterFile, 'EPUB', 'application/xhtml+xml');
                $this->packageMaker->appendManifestItem($manifestItem);
                $this->packageMaker->createSpineItemRef($manifestItem->getId());
            }

            $manifestItem = ManifestItem::fromFile($this->navegation->makeFile(), 'EPUB', 'application/xhtml+xml');
            $this->packageMaker->appendManifestItem($manifestItem);
        }

        return $this->packageMaker;
    }

    protected function generateContainer(): void
    {
        $container = new ContainerMaker($this->getPackageMaker()->makeFile());
        $container->makeFile();
    }

    protected function generateMimeType(): void
    {
        FileManager::getInstance()->saveFile('mimetype', self::MIME_TYPE);
    }

    /**
     * Save raw files withnot compressed
     * @param string $path
     * @return void
     */
    public function saveRaw(string $path): void
    {
        $this->generateEpub();
        FileManager::getInstance()->copyAllTo($path);
    }

    public function save(string $path, string $filename = null): void
    {
        $this->generateEpub();
        $filename = $filename ?: Str::slugify($this->getTitle()) . '.epub';
        FileManager::getInstance()->compressAllTo($filename, $path);
    }

    public function download()
    {
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
