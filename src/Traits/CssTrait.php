<?php

namespace Prhost\Epub3\Traits;

use Prhost\Epub3\Elements\Files\Css;

trait CssTrait
{
    protected function makeLink(Css $cssFile, \DOMDocument $document, string $relativePath = null): \DOMElement
    {
        $link = $document->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('type', 'text/css');
        $link->setAttribute('href', $cssFile->getRelativeEpubPath($relativePath));

        return $link;
    }
}
