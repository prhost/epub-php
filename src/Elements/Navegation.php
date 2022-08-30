<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use DOMDocument;
use DOMElement;
use Prhost\Epub3\Elements\Files\Css;
use Prhost\Epub3\Epub;
use Prhost\Epub3\Traits\CssTrait;

class Navegation
{
    use CssTrait;

    protected Epub $epub;

    /**
     * @var Css[]
     */
    protected array $cssFiles = [];

    /**
     * @var Menu[]
     */
    protected array $menus = [];

    public function __construct(Epub $epub)
    {
        $this->epub = $epub;
    }

    public function appendCss(Css $css): void
    {
        $this->cssFiles[] = $css;
    }

    public function createMainMenu(string $filePath, string $title): Menu
    {
        $this->appendMainMenu($menu = new Menu($filePath, $title));

        return $menu;
    }

    public function appendMainMenu(Menu $menu): self
    {
        $this->menus[] = &$menu;

        return $this;
    }

    public function generateXhtml(): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $html = $document->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $document->appendChild($html);

        $head = $document->createElement('head');
        $html->appendChild($head);

        $head->appendChild($document->createElement('title', $this->epub->getTitle()));

        foreach ($this->cssFiles as $cssFile) {
            $head->appendChild($this->makeLink($cssFile, $document));
        }

        $body = $document->createElement('body');
        $html->appendChild($body);

        $nav = $document->createElement('nav');
        $nav->setAttribute('epub:type', 'toc');
        $body->appendChild($nav);

        $nav->appendChild($document->createElement('h1', $this->epub->getTitle()));

        if ($this->menus) {
            $ol = $document->createElement('ol');
            $nav->appendChild($ol);

            $this->makeNavsOl($this->menus, $ol, $document);
        }

        return $document->saveXML();
    }

    /**
     * @param Menu[] $menus
     * @param DOMElement $ol
     * @param DOMDocument $document
     * @return void
     */
    protected function makeNavsOl(array $menus, DOMElement $ol, DOMDocument $document)
    {
        foreach ($menus as $menu) {
            $li = $document->createElement('li');
            $a = $document->createElement('a');
            $li->appendChild($a);
            $a->setAttribute('href', $menu->getFilePath());
            $a->nodeValue = $menu->getTitle();
            $ol->appendChild($li);
            if ($menu->hasSubmenus()) {
                $ol = $document->createElement('ol');
                $li->appendChild($ol);
                $this->makeNavsOl($menu->getSubmenus(), $ol, $document);
            }
        }
    }
}
