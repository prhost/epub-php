<?php

namespace Prhost\Epub\Makers;

use Prhost\Epub\Files\File;

abstract class MakerAbstract
{
    public const CSS_PATH = 'EPUB/css';

    public const IMAGES_PATH = 'EPUB/images';

    public const XHTML_PATH = 'EPUB/xhtml';

    abstract public function makeContent(): string;

    abstract public function makeFile(): File;
}
