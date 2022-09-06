<?php

declare(strict_types=1);

namespace Prhost\Epub;

use Prhost\Epub\Elements\ManifestItem;
use Prhost\Epub\Files\Chapter;
use Prhost\Epub\Helpers\Str;
use Prhost\Epub\Makers\ChapterMaker;
use Prhost\Epub\Makers\ContainerMaker;
use Prhost\Epub\Makers\CoverMaker;
use Prhost\Epub\Makers\NavegationMaker;
use Prhost\Epub\Makers\PackageMaker;

class Epub
{
    protected const MIME_TYPE = 'application/epub+zip';

    protected string $title;

    protected string $language;

    protected \DateTime $dcTermsModified;

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

    public function __construct(string $title, string $language = 'en', \DateTime $dcTermsModified = null)
    {
        $this->title = $title;
        $this->language = $language;
        $this->dcTermsModified = $dcTermsModified ?: new \DateTime('now');
    }

    public function nav(string $basePath = 'EPUB/xhtml', string $filename = null): NavegationMaker
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

    public function addChapter(string $title, string $content = null, string $filename = null, string $basePath = 'EPUB/xhtml'): self
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

            $this->packageMaker->createMetadataItem('dc:language', $this->language);
            $this->packageMaker->createMetadataItem('meta', $this->dcTermsModified->format('Y-m-d\TH:i:sp'), ['property' => 'dcterms:modified']);

            $coverFile = $this->coverMaker->makeFile();

            $this->packageMaker->appendManifestItem(
                ManifestItem::fromFile(
                    $coverFile,
                    'EPUB',
                    'application/xhtml+xml'
                )
            );

            $this->packageMaker->appendManifestItem(
                ManifestItem::fromFile(
                    $this->coverMaker->getImage(),
                    'EPUB',
                    null,
                    ['properties' => "cover-image"]
                )
            );

            $this->packageMaker->createMetadataItem('meta', null, [
                'name' => 'cover',
                'contet' => $this->coverMaker->getImage()->getFilename(),
            ]);

            foreach ($this->chaptersFiles as $chapterFile) {
                $manifestItem = ManifestItem::fromFile($chapterFile, 'EPUB', 'application/xhtml+xml');
                $this->packageMaker->appendManifestItem($manifestItem);
                $this->packageMaker->createSpineItemRef($manifestItem->getId());
            }

            $manifestItem = ManifestItem::fromFile($this->navegation->makeFile(), 'EPUB', 'application/xhtml+xml', ['properties' => 'nav']);
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
    public function saveRaw(string $path): string
    {
        $this->generateEpub();
        return FileManager::getInstance()->copyAllTo($path);
    }

    public function save(string $path, string $filename = null): string
    {
        $this->generateEpub();
        return FileManager::getInstance()->compressAllTo($this->getFilename($filename), $path);
    }

    public function download(string $filename = null)
    {
        $this->generateEpub();
        return FileManager::getInstance()->download($this->getFilename($filename));
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
    
    protected function getFilename(string $filename = null): string
    {
        return $filename ?: Str::slugify($this->getTitle()) . '.epub';
    }
}
