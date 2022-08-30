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
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><container/>');

        $xml->addAttribute('version', '1.0');
        $xml->addAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $rootfiles = $xml->addChild('rootfiles');
        $rootfile = $rootfiles->addChild('rootfile');
        $rootfile->addAttribute('full-path', rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->package->getFilename());
        $rootfile->addAttribute('media-type', "application/oebps-package+xml");

        return $xml->asXML();
    }
}
