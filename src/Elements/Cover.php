<?php
declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use DOMDocument;
use Prhost\Epub3\Elements\Files\Css;
use Prhost\Epub3\Elements\Files\Image;
use Prhost\Epub3\Epub;
use Prhost\Epub3\Traits\CssTrait;

class Cover
{
    use CssTrait;

    protected Image $image;
    protected Epub $epub;
    protected array $cssFiles = [];

    public function __construct(Image $image, Epub $epub)
    {
        $this->image = $image;
        $this->epub = $epub;
    }

    public function appendCss(Css $css): self
    {
        $this->cssFiles[] = $css;
        return $this;
    }

    public function generateXhtml(string $relativePath): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $html = $document->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $document->appendChild($html);

        $head = $document->createElement('head');
        $html->appendChild($head);

        foreach ($this->cssFiles as $cssFile) {
            $head->appendChild($this->makeLink($cssFile, $document, $relativePath));
        }

        $head->appendChild($document->createElement('title', $this->epub->getTitle()));

        $body = $document->createElement('body');
        $html->appendChild($body);

        $figure = $document->createElement('figure');
        $figure->setAttribute('id', 'cover-image');

        $img = $document->createElement('img');
        $img->setAttribute('src', $this->image->getRelativeEpubPath($relativePath));
        $img->setAttribute('alt', $this->epub->getTitle());

        $figure->appendChild($img);

        $body->appendChild($figure);

        return $document->saveXML();
    }
}