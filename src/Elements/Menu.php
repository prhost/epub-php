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

    public function appendSubMenu(string $filePath, string $title): Menu
    {
        $submenu = new Menu($filePath, $title);
        $this->submenus[] = &$submenu;

        return $submenu;
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
