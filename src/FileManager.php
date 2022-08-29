<?php

declare(strict_types=1);

namespace Prhost\Epub3;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

class FileManager
{
    /**
     * @var string
     */
    protected $savePath;

    protected string $rootDirName;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(string $rootDirName = null)
    {
        $this->rootDirName = $rootDirName ?: Uuid::uuid4()->toString();
    }

    /**
     * @param string $fileName
     * @param string|null $content
     * @param string|null $subPath
     * @return string return complete file path
     */
    public function saveFile(string $fileName, string $content = null, string $subPath = null): string
    {
        $pathFile = self::path($subPath ? ($subPath . DIRECTORY_SEPARATOR . $fileName) : $fileName);
        self::getFileystem()->mkdir(dirname($pathFile));
        self::getFileystem()->appendToFile($pathFile, $content);

        return $pathFile;
    }

    public function path(string $file = null): string
    {
        return $file ? ($this->getSavePath() . DIRECTORY_SEPARATOR . $this->rootDirName . DIRECTORY_SEPARATOR . $file) : self::getSavePath();
    }

    protected function getFileystem(): Filesystem
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    public function setSavePath(string $path): void
    {
        $this->savePath = $path;
    }

    public function getSavePath(): string
    {
        if (null === $this->savePath) {
            $this->savePath = sys_get_temp_dir();
        }

        return rtrim($this->savePath, DIRECTORY_SEPARATOR);
    }
}
