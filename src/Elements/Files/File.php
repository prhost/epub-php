<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements\Files;

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

    public function getRelativeEpubPath(string $relativePath = null): string
    {
        return rtrim(FileManager::getInstance()->relativePath($this, $relativePath), DIRECTORY_SEPARATOR);
    }
}
