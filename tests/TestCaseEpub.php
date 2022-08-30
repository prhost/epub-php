<?php

namespace Prhost\Epub3\Tests;

use PHPUnit\Framework\TestCase;

class TestCaseEpub extends TestCase
{
    public function getSamplePath(string $filePath): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . $filePath;
    }
}
