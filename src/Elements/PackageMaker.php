<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use Prhost\Epub3\Epub;
use Ramsey\Uuid\Uuid;

class PackageMaker
{
    public const UNIQUE_IDENTIFIER = 'pub-id';

    /**
     * @var ManifestItem[]
     */
    protected array $manifestItems = [];

    /**
     * @var MetadataItem[]
     */
    protected array $metadataItems = [];

    /**
     * @var SpineItemRef[]
     */
    protected array $spineItems = [];

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var Epub
     */
    protected Epub $epub;

    public function __construct(Epub $epub, string $identifier = null)
    {
        $this->epub = $epub;
        $this->identifier = $identifier ?: $this->generateIdentifier();
    }

    protected function generateIdentifier(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function createManifestItem(string $id, string $href, string $mediaType, array $attributes = []): ManifestItem
    {
        $this->appendManifestItem($item = new ManifestItem($id, $href, $mediaType, $attributes));

        return $item;
    }

    public function appendManifestItem(ManifestItem $item): self
    {
        $this->manifestItems[] = $item;

        return $this;
    }

    public function createMetadataItem(string $tagName, string $value = null, array $attributes = []): MetadataItem
    {
        $this->appendMetadataItem($item = new MetadataItem($tagName, $value, $attributes));

        return $item;
    }

    public function appendMetadataItem(MetadataItem $item): self
    {
        $this->metadataItems[] = $item;

        return $this;
    }

    public function createSpineItemRef(string $idref, array $attributes = []): SpineItemRef
    {
        $this->appendSpineItemRef($item = new SpineItemRef($idref, $attributes));

        return $item;
    }

    public function appendSpineItemRef(SpineItemRef $item): self
    {
        $this->spineItems[] = $item;

        return $this;
    }

    public function generateXml(): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $package = $document->createElement('package');
        $document->appendChild($package);

        $package->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');
        $package->setAttribute('version', '3.0');
        $package->setAttribute('unique-identifier', self::UNIQUE_IDENTIFIER);
        $package->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

        $metadata = $document->createElement('metadata');
        $package->appendChild($metadata);

        $identifier = $document->createElement('dc:identifier', 'urn:uuid:' . $this->identifier);
        $identifier->setAttribute('id', self::UNIQUE_IDENTIFIER);
        $metadata->appendChild($identifier);

        $metadata->appendChild($document->createElement('dc:title', $this->epub->getTitle()));

        foreach ($this->metadataItems as $metadataItem) {
            $package->appendChild($metadataItem->make($document));
        }

        $manifest = $document->createElement('manifest');
        $package->appendChild($manifest);

        foreach ($this->manifestItems as $manifestItem) {
            $package->appendChild($manifestItem->make($document));
        }

        $spine = $document->createElement('spine');
        $spine->setAttribute('toc', 'ncx');
        $package->appendChild($spine);

        foreach ($this->spineItems as $spineItem) {
            $package->appendChild($spineItem->make($document));
        }

        return $document->saveXML();
    }
}
