<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use SimpleXMLElement;

class Container
{
    public static function generateXml(): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><container/>');

        $xml->addAttribute('version', '1.0');
        $xml->addAttribute('xmlns', 'urn:oasis:names:tc:opendocument:xmlns:container');

        $rootfiles = $xml->addChild('rootfiles');
        $rootfile = $rootfiles->addChild('rootfile');
        $rootfile->addAttribute('full-path', "EPUB/package.opf");
        $rootfile->addAttribute('media-type', "application/oebps-package+xml");

        return $xml->asXML();
    }
}
