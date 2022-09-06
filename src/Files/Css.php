<?php

declare(strict_types=1);

namespace Prhost\Epub\Files;

class Css extends File
{
    public const DEFAULT_EPUB_PATH = 'EPUB/css';

    public function __construct(string $filename, string $epubSubPath = null)
    {
        parent::__construct($filename, $epubSubPath ?: self::DEFAULT_EPUB_PATH);
    }
}
