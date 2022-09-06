<?php

declare(strict_types=1);

namespace Prhost\Epub\Elements;

abstract class ElementItem
{
    protected string $tagName;

    protected $value;

    protected array $attributes = [];

    public function __construct(string $tagName, $value = null, array $attributes = [])
    {
        $this->tagName = $tagName;
        $this->value = $value;
        $this->attributes = $attributes;
    }

    public function make(\DOMDocument $document): \DOMElement
    {
        $item = $document->createElement($this->tagName);

        if ($this->value) {
            $item->nodeValue = $this->value;
        }

        foreach ($this->attributes as $key => $attribute) {
            $item->setAttribute($key, $attribute);
        }

        return $item;
    }
}
