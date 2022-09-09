<?php

declare(strict_types=1);

namespace Prhost\Epub;

use PhpZip\ZipFile;
use Prhost\Epub\Files\File;
use Prhost\Epub\Traits\Singleton;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

class FileManager
{
    use Singleton;

    /**
     * @var string
     */
    protected $savePath;

    /**
     * @var string
     */
    protected $rootDirName;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function getRootDirName(): string
    {
        return $this->rootDirName = $this->rootDirName ?: Uuid::uuid4()->toString();
    }

    public function setRootDirName(string $rootDirName): void
    {
        $this->rootDirName = $rootDirName;
    }

    /**
     * @param string $fileName
     * @param string|null $content
     * @param string|null $subPath
     * @return string return complete file path
     */
    public function saveFile(string $fileName, string $content = null, string $subPath = null): string
    {
        $pathFile = self::realPath($fileName, $subPath);
        self::getFileystem()->mkdir(dirname($pathFile));
        self::getFileystem()->dumpFile($pathFile, $content);

        return $pathFile;
    }

    public function download(string $filename): void
    {
        $zipFile = new ZipFile();

        try {
            $zipFile
                ->addDirRecursive($this->realPath('/'))
                ->outputAsAttachment($filename);
        } catch (\PhpZip\Exception\ZipException $e) {
            throw new $e();
        } finally {
            $zipFile->close();
        }
    }

    public function compressAllTo(string $filename, string $path): string
    {
        $zipFile = new ZipFile();

        self::getFileystem()->mkdir($path);

        $filePath = $path . DIRECTORY_SEPARATOR . rtrim($filename, '.epub') . '.epub';

        try {
            $zipFile
                ->addDirRecursive($this->realPath('/'))
                ->saveAsFile($filePath)
                ->close();
        } catch (\PhpZip\Exception\ZipException $e) {
            throw new $e();
        } finally {
            $zipFile->close();
        }

        return $filePath;
    }

    /**
     * Copy all files and paths to other path
     * @param string $path
     * @return void
     */
    public function copyAllTo(string $path): string
    {
        self::getFileystem()->mirror($this->realPath(), $path);

        return $path;
    }

    public function copyToEpub(string $filePath, string $epubSubPath = null): string
    {
        $fileName = basename($filePath);
        $targetFile = $this->realPath($fileName, $epubSubPath);
        self::getFileystem()->mkdir(dirname($targetFile));
        self::getFileystem()->copy($filePath, $targetFile);

        return $targetFile;
    }

    public function realPath(string $file = null, string $epubSubPath = null): string
    {
        if ($epubSubPath) {
            $file = trim($epubSubPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        }

        $epubPath = $this->getSavePath() . DIRECTORY_SEPARATOR . $this->getRootDirName();

        return $file ? ($epubPath . DIRECTORY_SEPARATOR . $file) : $epubPath;
    }

    public function relativePath(File $file, string $relativePath = null): string
    {
        $path = rtrim(self::getFileystem()->makePathRelative($file->getPath(), $this->realPath(null, $relativePath)), DIRECTORY_SEPARATOR);

        return $path . DIRECTORY_SEPARATOR . $file->getFilename();
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

    public function setTempSavePath(): void
    {
        $this->setSavePath(sys_get_temp_dir());
    }
}
