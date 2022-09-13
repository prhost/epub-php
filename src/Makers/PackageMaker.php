<?php

declare(strict_types=1);

namespace Prhost\Epub\Makers;

use Prhost\Epub\Elements\ManifestItem;
use Prhost\Epub\Elements\MetadataItem;
use Prhost\Epub\Elements\SpineItemRef;
use Prhost\Epub\Files\Package;
use Ramsey\Uuid\Uuid;

class PackageMaker extends MakerAbstract
{
    public const UNIQUE_IDENTIFIER = 'pub-id';

    protected const FILENAME = 'package.opf';

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

    protected string $basePath;

    public function __construct(string $title, string $identifier = null, string $basePath = '')
    {
        $this->title = $title;
        $this->identifier = $identifier ?: $this->generateIdentifier();
        $this->basePath = $basePath;
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

    public function makeContent(): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $package = $document->createElement('package');
        $document->appendChild($package);

        $package->setAttribute('xmlns', 'http://www.idpf.org/2007/opf');
        $package->setAttribute('version', '3.0');
        $package->setAttribute('unique-identifier', self::UNIQUE_IDENTIFIER);

        $metadata = $document->createElement('metadata');
        $metadata->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

        $package->appendChild($metadata);

        $identifier = $document->createElement('dc:identifier', 'urn:uuid:' . $this->identifier);
        $identifier->setAttribute('id', self::UNIQUE_IDENTIFIER);
        $metadata->appendChild($identifier);

        $metadata->appendChild($document->createElement('dc:title', $this->title));

        foreach ($this->metadataItems as $metadataItem) {
            $metadata->appendChild($metadataItem->make($document));
        }

        $manifest = $document->createElement('manifest');
        $package->appendChild($manifest);

        foreach ($this->manifestItems as $manifestItem) {
            $manifest->appendChild($manifestItem->make($document));
        }

        $spine = $document->createElement('spine');
        $package->appendChild($spine);

        foreach ($this->spineItems as $spineItem) {
            $spine->appendChild($spineItem->make($document));
        }

        return $document->saveXML();
    }

    public function makeFile(): Package
    {
        return Package::makeFromContent(self::FILENAME, $this->makeContent(), $this->basePath);
    }
}
