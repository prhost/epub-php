<?php

declare(strict_types=1);

namespace Prhost\Epub\Makers;

use Prhost\Epub\Files\Container;
use Prhost\Epub\Files\Package;

class ContainerMaker extends MakerAbstract
{
    protected string $filenama = 'container.xml';

    protected string $basePath = 'META-INF';

    protected Package $package;

    public function __construct(Package $package, string $filenama = null, string $basePath = null)
    {
        $this->package = $package;
        if ($filenama) {
            $this->filenama = $filenama;
        }
        if ($basePath) {
            $this->basePath = $basePath;
        }
    }

    public function makeContent(): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $container = $document->createElement('container');
        $document->appendChild($container);

        $container->setAttribute('version', '1.0');
        $container->setAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $rootfiles = $document->createElement('rootfiles');
        $container->appendChild($rootfiles);
        $rootfile = $document->createElement('rootfile');
        $rootfiles->appendChild($rootfile);

        $rootfile->setAttribute('full-path', $this->package->getRelativeEpubPath(''));
        $rootfile->setAttribute('media-type', "application/oebps-package+xml");

        return $document->saveXML();
    }

    public function makeFile(): Container
    {
        return Container::makeFromContent($this->getFilenama(), $this->makeContent(), $this->getBasePath());
    }

    /**
     * @return string
     */
    public function getFilenama(): string
    {
        return $this->filenama;
    }

    /**
     * @param string $filenama
     */
    public function setFilenama(string $filenama): void
    {
        $this->filenama = $filenama;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }
}
