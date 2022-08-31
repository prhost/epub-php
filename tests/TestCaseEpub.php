<?php

namespace Prhost\Epub3\Tests;

use PHPUnit\Framework\TestCase;

class TestCaseEpub extends TestCase
{
    public function getFilePath(string $filePath): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $filePath;
    }
}
