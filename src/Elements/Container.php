<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use Prhost\Epub3\Elements\Files\Package;
use SimpleXMLElement;

class Container
{
    protected Package $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function generateXml(string $basePath): string
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

        $rootfile->setAttribute('full-path', rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->package->getFilename());
        $rootfile->setAttribute('media-type', "application/oebps-package+xml");

        return $document->saveXML();
    }
}
