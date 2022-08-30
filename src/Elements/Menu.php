<?php
declare(strict_types=1);

namespace Prhost\Epub3\Elements;

class Menu
{
    protected array $submenus = [];

    protected string $filePath;

    protected string $title;

    public function __construct(string $filePath, string $title)
    {
        $this->filePath = $filePath;
        $this->title = $title;
    }

    public function createSubMenu(string $filePath, string $title): Menu
    {
        $this->appendSubMenu($submenu = new Menu($filePath, $title));
        return $submenu;
    }

    public function appendSubMenu(Menu $submenu): self
    {
        $this->submenus[] = $submenu;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getSubmenus(): array
    {
        return $this->submenus;
    }

    public function hasSubmenus(): bool
    {
        return count($this->submenus) > 0;
    }
}