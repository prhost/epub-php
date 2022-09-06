<?php

declare(strict_types=1);

namespace Prhost\Epub\Makers;

use DOMDocument;
use Prhost\Epub\Files\Cover;
use Prhost\Epub\Files\Css;
use Prhost\Epub\Files\Image;
use Prhost\Epub\Helpers\Str;
use Prhost\Epub\Traits\AssetTrait;

class CoverMaker extends MakerAbstract
{
    use AssetTrait {
        appendCss as appendCssTrait;
    }

    /**
     * @var Image
     */
    protected $image;

    protected string $title;

    protected string $basePath;

    protected string $filename;

    public function __construct(string $title, string $filename = null, string $basePath = '')
    {
        $this->title = $title;
        $this->basePath = $basePath;
        $this->filename = $filename ? rtrim($filename, '.xhtml') . '.xhtml' : Str::slugify($title) . '.xhtml';
    }

    public function appendCss(Css $css): self
    {
        return $this->appendCssTrait($css, $this->basePath);
    }

    public function setImage(string|Image $image, string $epubSubPath = 'EPUB/images'): self
    {
        if ($image instanceof Image) {
            $this->image = $image;
        } else {
            $this->image = new Image($image, $epubSubPath);
        }

        return $this;
    }

    public function makeContent(): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $html = $document->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $document->appendChild($html);

        $head = $document->createElement('head');
        $html->appendChild($head);

        $this->appendLinksInHead($head, $document);

        $head->appendChild($document->createElement('title', $this->title));

        $body = $document->createElement('body');
        $html->appendChild($body);

        $figure = $document->createElement('figure');
        $figure->setAttribute('id', 'cover-image');

        $img = $document->createElement('img');
        $img->setAttribute('src', $this->image->getRelativeEpubPath($this->basePath));
        $img->setAttribute('alt', $this->title);

        $figure->appendChild($img);

        $body->appendChild($figure);

        return $document->saveXML();
    }

    public function makeFile(): Cover
    {
        return Cover::makeFromContent($this->filename, $this->makeContent(), $this->basePath);
    }

    /**
     * @return Image
     */
    public function getImage(): Image
    {
        return $this->image;
    }
}
