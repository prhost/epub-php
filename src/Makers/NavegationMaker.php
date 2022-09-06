<?php

declare(strict_types=1);

namespace Prhost\Epub\Makers;

use DOMDocument;
use DOMElement;
use Prhost\Epub\Elements\Menu;
use Prhost\Epub\Files\Nav;
use Prhost\Epub\Traits\AssetTrait;

class NavegationMaker extends MakerAbstract
{
    use AssetTrait;

    protected const DEFAULT_EPUB_FILENAME = 'nav.xhtml';

    /**
     * @var Menu[]
     */
    protected array $menus = [];

    protected string $title;

    protected string|null $basePath;

    protected string $filename;

    public function __construct(string $title, string $filename = null, string $basePath = null)
    {
        $this->title = $title;
        $this->basePath = $basePath;
        $this->filename = $filename ?: self::DEFAULT_EPUB_FILENAME;
    }

    public function createMainMenu(string $filePath, string $title): Menu
    {
        $menu = new Menu($filePath, $title);
        $this->appendMainMenu($menu);

        return $menu;
    }

    public function appendMainMenu(Menu $menu): self
    {
        $this->menus[] = &$menu;

        return $this;
    }

    public function makeContent(): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $implementation = new \DOMImplementation();
        $document->appendChild($implementation->createDocumentType('html'));

        $html = $document->createElement('html');
        $html->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        $html->setAttribute('xmlns:epub', 'http://www.idpf.org/2007/ops');
        $document->appendChild($html);

        $head = $document->createElement('head');
        $html->appendChild($head);

        $head->appendChild($document->createElement('title', $this->title));

        $this->appendLinksInHead($head, $document);

        $body = $document->createElement('body');
        $html->appendChild($body);

        $nav = $document->createElement('nav');
        $nav->setAttribute('epub:type', 'toc');
        $body->appendChild($nav);

        $nav->appendChild($document->createElement('h1', $this->title));

        if ($this->menus) {
            $this->makeNavs($this->menus, $nav, $document);
        }

        return $document->saveXML();
    }

    /**
     * @param Menu[] $menus
     * @param DOMElement $parent
     * @param DOMDocument $document
     * @return void
     */
    protected function makeNavs(array $menus, DOMElement &$parent, DOMDocument $document)
    {
        $ol = $document->createElement('ol');
        $parent->appendChild($ol);

        foreach ($menus as $menu) {
            $li = $document->createElement('li');
            $a = $document->createElement('a');
            $li->appendChild($a);
            $a->setAttribute('href', $menu->getFilePath());
            $a->nodeValue = $menu->getTitle();
            $ol->appendChild($li);

            if ($menu->hasSubmenus()) {
                $this->makeNavs($menu->getSubmenus(), $li, $document);
            }
        }
    }

    public function makeFile(): Nav
    {
        return Nav::makeFromContent($this->filename, $this->makeContent(), $this->basePath);
    }

    /**
     * @return Menu[]
     */
    public function getMenus(): array
    {
        return $this->menus;
    }
}
