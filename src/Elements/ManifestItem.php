<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use Prhost\Epub3\Elements\Files\File;

class ManifestItem extends ElementItem
{
    protected string $id;
    protected string $href;
    protected string $mediaType;

    public function __construct(string $id, string $href, $mediaType, array $attributes = [])
    {
        $attributes['id'] = $this->id = $id;
        $attributes['href'] = $this->href = $href;
        $attributes['mediaType'] = $this->mediaType = $mediaType;

        parent::__construct('item', null, $attributes);
    }

    public static function fromFile(File $file, string $relativePath): self
    {
        $id = uniqid();
        return new self('id-' . $id, $file->getRelativeEpubPath($relativePath), mime_content_type($file->getRealPath()));
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
