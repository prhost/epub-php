<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use Prhost\Epub3\FileManager;

abstract class File extends \SplFileInfo
{
    public function __construct(string $filename)
    {
        if (is_file($filename)) {
            parent::__construct($filename);
        } else {
            parent::__construct(FileManager::path($filename));
        }
    }
}
