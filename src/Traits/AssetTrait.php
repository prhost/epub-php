<?php

namespace Prhost\Epub3\Traits;

use Prhost\Epub3\Files\Css;
use Prhost\Epub3\Files\File;

trait AssetTrait
{
    /**
     * @var array
     */
    protected array $links = [];

    /**
     * @param Css $css
     * @param string|null $relativePath
     * @return self
     */
    public function addCss(Css $css, string $relativePath = null): self
    {
        $this->appendCss($css, $relativePath);
        return $this;
    }

    public function createCss(string $filename, string $relativePath = null): Css
    {
        $css = new Css($filename, $relativePath);
        $this->appendCss($css, $relativePath);

        return $css;
    }

    public function appendLink(File $file, string $rel = null, string $type = null, array $attributes = [], string $relativePath = null): self
    {
        $this->links[$file->getRealPath()] = [
            'rel'          => $rel,
            'type'         => $type,
            'attributes'   => $attributes,
            'file'         => $file,
            'relativePath' => $relativePath
        ];

        return $this;
    }

    public function appendCss(Css $css, string $relativePath = null): self
    {
        $this->appendLink($css, 'stylesheet', 'text/css', [], $relativePath);
        return $this;
    }

    protected function appendLinksInHead(&$head, $document)
    {
        foreach ($this->links as $link) {
            $head->appendChild($this->makeLink($link['file'], $document, $link['rel'], $link['type'], $link['attributes'], $link['relativePath']));
        }
    }

    protected function makeLink(File $file, \DOMDocument $document, string $rel = null, string $type = null, array $attributes = [], string $relativePath = null): \DOMElement
    {
        $link = $document->createElement('link');

        if ($rel) {
            $link->setAttribute('rel', $rel);
        }

        if ($type) {
            $link->setAttribute('type', $type);
        }

        foreach ($attributes as $key => $attribute) {
            $link->setAttribute($key, $attribute);
        }

        $link->setAttribute('href', $file->getRelativeEpubPath($relativePath));

        return $link;
    }

    protected function makeCss(Css $cssFile, \DOMDocument $document, string $relativePath = null): \DOMElement
    {
        return $this->makeLink($cssFile, $document, 'stylesheet', 'text/css', [], $relativePath);
    }
}
