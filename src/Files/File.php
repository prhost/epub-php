<?php

declare(strict_types=1);

namespace Prhost\Epub3\Files;

use Prhost\Epub3\FileManager;

abstract class File extends \SplFileInfo
{
    /**
     * @var string
     */
    protected $epubSubPath;

    protected $filePath;

    public function __construct(string $filename, string $epubSubPath = null)
    {
        $this->filePath = FileManager::getInstance()->copyToEpub($filename, $epubSubPath);

        parent::__construct($this->filePath);

        $this->epubSubPath = $epubSubPath;
    }

    public static function makeFromContent(string $filename, string $content = null, string $epubSubPath = null): static
    {
        $filePath = FileManager::getInstance()->saveFile($filename, $content, $epubSubPath);
        $file = new static($filePath, $epubSubPath);
        $file->setEpubSubPath($epubSubPath);

        return $file;
    }

    public function getRelativeEpubPath(string|null $relativePath): string
    {
        return rtrim(FileManager::getInstance()->relativePath($this, $relativePath), DIRECTORY_SEPARATOR);
    }

    public function getContent(): ?string
    {
        return file_get_contents($this->getPath());
    }

    /**
     * @return string|null
     */
    public function getEpubSubPath(): ?string
    {
        return $this->epubSubPath;
    }

    /**
     * @param string|null $epubSubPath
     */
    public function setEpubSubPath(?string $epubSubPath): void
    {
        $this->epubSubPath = $epubSubPath;
    }
}
