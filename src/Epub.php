<?php

declare(strict_types=1);

namespace Prhost\Epub3;

use Prhost\Epub3\Elements\Navegation;
use Symfony\Component\Filesystem\Filesystem;

class Epub
{
    protected const MIME_TYPE = 'application/epub+zip';

    protected string $title;

    /**
     * @var Navegation
     */
    protected $navegation;

    public function __construct(string $title, string $version = null)
    {
        $this->title = $title;
    }

    public function nav(): Navegation
    {
        if (null === $this->navegation) {
            $this->navegation = new Navegation($this);
        }

        return $this->navegation;
    }

    protected function generateEpub(): void
    {
    }

    protected function generateMimeType(): void
    {
    }

    public function save(string $name, ?string $path): void
    {
    }

    public function download()
    {
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
