<?php

namespace Prhost\Epub3\Tests;

use PHPUnit\Framework\TestCase;
use Prhost\Epub3\Elements\Container;
use Prhost\Epub3\Elements\Navegation;
use Prhost\Epub3\Epub;
use Prhost\Epub3\FileManager;

class EpubTest extends TestCase
{
    /**
     * @var string
     */
    protected $navXmlContent = '<?xml version="1.0" encoding="UTF-8"?><html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops"><head><title>test</title></head><body><nav epub:type="toc"><h1>test</h1><ol><li><a href="chapter1.xhtml">Chapter 1</a></li><li><a href="chapter2.xhtml">Chapter 2</a><ol><li><a href="chapter3.xhtml">Chapter 3</a></li><li><a href="chapter4.xhtml">Chapter 4</a></li><li><a href="chapter5.xhtml">Chapter 5</a><ol><li><a href="chapter6.xhtml">Chapter 6</a></li></ol></li></ol></li></ol></nav></body></html>';

    public function testFileManager(): void
    {
        $fileManager = new FileManager();
        $this->assertEquals(sys_get_temp_dir(), $fileManager->getSavePath());

        $fileManager->setSavePath('/test');
        $this->assertEquals('/test', $fileManager->getSavePath());

        $fileManager = new FileManager();
        $content = '<h1>Test HTML</h1>';
        $filePath = $fileManager->saveFile('test.html', $content);
        $this->assertFileExists($filePath);
        $this->assertStringEqualsFile($filePath, $content);

        $filePath = $fileManager->saveFile('test2.html', $content, 'META-INF');
        $this->assertFileExists($filePath);
        $this->assertStringEqualsFile($filePath, $content);
    }

    public function testContainerGenerate(): void
    {
        $xml = Container::generateXml();
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/epub-samples/basic-v3plus2/META-INF/container.xml', $xml);
    }

    public function testNavegation(): void
    {
        $epub = new Epub('test');
        $this->assertInstanceOf(Navegation::class, $epub->nav());

        $epub->nav()->appendMainMenu('chapter1.xhtml', 'Chapter 1');
        $mainMenu2 = $epub->nav()->appendMainMenu('chapter2.xhtml', 'Chapter 2');

        $mainMenu2->appendSubMenu('chapter3.xhtml', 'Chapter 3');
        $mainMenu2->appendSubMenu('chapter4.xhtml', 'Chapter 4');

        $subMenu5 = $mainMenu2->appendSubMenu('chapter5.xhtml', 'Chapter 5');
        $subMenu5->appendSubMenu('chapter6.xhtml', 'Chapter 6');

        $xhtml = $epub->nav()->generateXhtml();

        $fileManager = new FileManager();
        $filePath = $fileManager->saveFile('nav.xhtml', $xhtml);
        $this->assertStringEqualsFile($filePath, $xhtml);

        $this->assertXmlStringEqualsXmlString($this->navXmlContent, $xhtml);
    }
}
