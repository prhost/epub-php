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

    protected string $language = 'en';

    protected $dcTermsModified;

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

    /**
     * The book's creator
     * @var string
     */
    protected string $creator = '';

    /**
     * The book's rights
     * @var string
     */
    protected string $rights = '';

    /**
     * This is the book's publisher
     * @var string
     */
    protected string $publisher = '';

    public function __construct(string $title)
    {
        $this->title = $title;
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

    public function addChapter(string $title, string $content = null, string $filename = null, string $basePath = 'EPUB/xhtml'): ChapterMaker
    {
        $chapter = new ChapterMaker($title, $content, $filename, $basePath);
        $this->appendChapter($chapter);

        return $chapter;
    }

    public function appendChapter(ChapterMaker &$chapterMaker): self
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

    public function getPackage(): PackageMaker
    {
        if (null === $this->packageMaker) {
            $this->packageMaker = new PackageMaker($this->getTitle(), null, 'EPUB');

            $this->packageMaker->createMetadataItem('dc:language', $this->language);
            $this->packageMaker->createMetadataItem('meta', $this->getDcTermsModified()->format('Y-m-d\TH:i:sp'), ['property' => 'dcterms:modified']);

            if ($this->getCreator()) {
                $this->packageMaker->createMetadataItem('dc:creator', $this->getCreator());
            }

            if ($this->getPublisher()) {
                $this->packageMaker->createMetadataItem('dc:publisher', $this->getPublisher());
            }

            if ($this->getRights()) {
                $this->packageMaker->createMetadataItem('dc:rights', $this->getRights());
            }

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

            $manifestItem = ManifestItem::fromFile($this->nav()->makeFile(), 'EPUB', 'application/xhtml+xml', ['properties' => 'nav']);
            $this->packageMaker->appendManifestItem($manifestItem);
        }

        return $this->packageMaker;
    }

    protected function generateContainer(): void
    {
        $container = new ContainerMaker($this->getPackage()->makeFile());
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

    public function download(string $filename = null): void
    {
        $this->generateEpub();
        FileManager::getInstance()->download($this->getFilename($filename));
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

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDcTermsModified(): \DateTime
    {
        return $this->dcTermsModified ?: new \DateTime('now');
    }

    /**
     * @param \DateTime $dcTermsModified
     */
    public function setDcTermsModified(\DateTime $dcTermsModified): self
    {
        $this->dcTermsModified = $dcTermsModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreator(): string
    {
        return $this->creator;
    }

    /**
     * @param string $creator
     */
    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return string
     */
    public function getRights(): string
    {
        return $this->rights;
    }

    /**
     * @param string $rights
     */
    public function setRights(string $rights): self
    {
        $this->rights = $rights;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublisher(): string
    {
        return $this->publisher;
    }

    /**
     * @param string $publisher
     */
    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }
}
