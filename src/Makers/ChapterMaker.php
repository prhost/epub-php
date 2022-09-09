<?php

declare(strict_types=1);

namespace Prhost\Epub\Makers;

use Prhost\Epub\Files\Chapter;
use Prhost\Epub\Files\File;
use Prhost\Epub\Helpers\Str;
use Prhost\Epub\Traits\AssetTrait;

class ChapterMaker extends MakerAbstract
{
    use AssetTrait;

    protected string $title;

    protected string $filename;

    protected string $content;

    protected string $basePath;

    public function __construct(string $title, string $content = '', string $filename = null, string $basePath = '')
    {
        $this->title = $title;
        $this->filename = $filename ? rtrim($filename, '.xhtml') . '.xhtml' : Str::slugify($title) . '.xhtml';
        $this->content = $content;
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    public function makeContent(): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $implementation = new \DOMImplementation();
        $document->appendChild($implementation->createDocumentType('html'));

        $html = $document->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $document->appendChild($html);

        $head = $document->createElement('head');
        $html->appendChild($head);

        $this->appendLinksInHead($head, $document);

        $head->appendChild($document->createElement('title', $this->getTitle()));

        $body = $document->createElement('body');
        $html->appendChild($body);

        $bodyContent = $document->createDocumentFragment();

        $cntentDom = new \DOMDocument();
        $cntentDom->loadHtml($this->getContent());
        $bodyContent->appendChild($document->importNode($cntentDom->documentElement, true));

        $body->appendChild($bodyContent);

        return $document->saveXML();
    }

    public function makeFile(): File
    {
        return Chapter::makeFromContent($this->filename, $this->makeContent(), $this->basePath);
    }
}
