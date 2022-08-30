<?php

declare(strict_types=1);

namespace Prhost\Epub3\Elements;

class SpineItemRef extends ElementItem
{
    public function __construct(string $idref, array $attributes = [])
    {
        $attributes['idref'] = $idref;
        parent::__construct('itemref', null, $attributes);
    }
}
