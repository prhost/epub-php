<?php

namespace Prhost\Epub3\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;

class TestCaseEpub extends TestCase
{
    protected $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->faker = Factory::create('pt_BR');

        parent::__construct($name, $data, $dataName);
    }

    public function getFilePath(string $filePath): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $filePath;
    }

    public function getFileContent(string $filePath): string
    {
        return file_get_contents($this->getFilePath($filePath));
    }
}
