<?php

namespace Prhost\Epub3\Helpers;

class Str
{
    public static function slugify($text = null, $divider = '-'): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', $divider, $text)));
    }
}