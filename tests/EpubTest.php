<?php

namespace Prhost\Epub3\Tests;

use Prhost\Epub3\Elements\Container;
use Prhost\Epub3\Elements\Cover;
use Prhost\Epub3\Elements\Files\Css;
use Prhost\Epub3\Elements\Files\File;
use Prhost\Epub3\Elements\Files\Image;
use Prhost\Epub3\Elements\Files\Package;
use Prhost\Epub3\Elements\Navegation;
use Prhost\Epub3\Epub;
use Prhost\Epub3\FileManager;

class EpubTest extends TestCaseEpub
{
    protected $navXhtmlContent = '<?xml version="1.0" encoding="UTF-8"?><html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops"><head><title>test</title></head><body><nav epub:type="toc"><h1>test</h1><ol><li><a href="chapter1.xhtml">Chapter 1</a></li><li><a href="chapter2.xhtml">Chapter 2</a><ol><li><a href="chapter3.xhtml">Chapter 3</a></li><li><a href="chapter4.xhtml">Chapter 4</a></li><li><a href="chapter5.xhtml">Chapter 5</a><ol><li><a href="chapter6.xhtml">Chapter 6</a></li></ol></li></ol></li></ol></nav></body></html>';
    protected $coverXhtmlContent = '<?xml version="1.0" encoding="UTF-8"?><html xmlns="http://www.w3.org/1999/xhtml"><head><link rel="stylesheet" type="text/css" href="../css/cover.css"/><title>Test Title</title></head><body><figure id="cover-image"><img src="../images/sample-cover.jpg" alt="Test Title"/></figure></body></html>';

    public function testFileManager(): void
    {
        $fileManager = FileManager::getInstance();
        $this->assertEquals(sys_get_temp_dir(), $fileManager->getSavePath());

        $fileManager->setSavePath('/test');
        $this->assertEquals('/test', $fileManager->getSavePath());

        $fileManager = FileManager::getInstance();
        $fileManager->setTempSavePath();
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
        $container = new Container(new Package($this->getSamplePath('epub-basic-v3plus2/EPUB/package.opf'), 'EPUB'));
        $xml = $container->generateXml('EPUB');

        $this->assertXmlStringEqualsXmlFile($this->getSamplePath('epub-basic-v3plus2/META-INF/container.xml'), $xml);
    }

    public function testNavegation(): void
    {
        $epub = new Epub('test');
        $this->assertInstanceOf(Navegation::class, $epub->nav());

        $epub->nav()->createMainMenu('chapter1.xhtml', 'Chapter 1');
        $mainMenu2 = $epub->nav()->createMainMenu('chapter2.xhtml', 'Chapter 2');

        $mainMenu2->createSubMenu('chapter3.xhtml', 'Chapter 3');
        $mainMenu2->createSubMenu('chapter4.xhtml', 'Chapter 4');

        $subMenu5 = $mainMenu2->createSubMenu('chapter5.xhtml', 'Chapter 5');
        $subMenu5->createSubMenu('chapter6.xhtml', 'Chapter 6');

        $xhtml = $epub->nav()->generateXhtml();

        $filePath = FileManager::getInstance()->saveFile('nav.xhtml', $xhtml, 'EPUB');
        $this->assertStringEqualsFile($filePath, $xhtml);

        $this->assertXmlStringEqualsXmlString($this->navXhtmlContent, $xhtml);
    }

    public function testFile(): void
    {
        $imagePath = $this->getSamplePath('covers/sample-cover.jpg');
        $image = new Image($imagePath, 'EPUB/images');
        $this->assertInstanceOf(File::class, $image);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInstanceOf(\SplFileInfo::class, $image);
        $this->assertFileExists($image->getPath());
    }

    public function testCover(): void
    {
        $cover = new Cover(new Image($this->getSamplePath('covers/sample-cover.jpg'), 'EPUB/images'), new Epub('Test Title'));
        $cover->appendCss(new Css($this->getSamplePath('epub-basic-v3plus2/EPUB/css/cover.css'), 'EPUB/css'));

        $xhtml = $cover->generateXhtml('EPUB/xhtml');
        $this->assertXmlStringEqualsXmlString($this->coverXhtmlContent, $xhtml);

        $filePath = FileManager::getInstance()->saveFile('cover.xhtml', $xhtml, 'EPUB');
        $this->assertStringEqualsFile($filePath, $xhtml);
    }
}
