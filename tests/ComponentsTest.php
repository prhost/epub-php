<?php

namespace Prhost\Epub\Tests;

use Prhost\Epub\Elements\ManifestItem;
use Prhost\Epub\Epub;
use Prhost\Epub\FileManager;
use Prhost\Epub\Files\Css;
use Prhost\Epub\Files\File;
use Prhost\Epub\Files\Image;
use Prhost\Epub\Files\Package;
use Prhost\Epub\Helpers\Str;
use Prhost\Epub\Makers\ChapterMaker;
use Prhost\Epub\Makers\ContainerMaker;
use Prhost\Epub\Makers\CoverMaker;
use Prhost\Epub\Makers\NavegationMaker;
use Prhost\Epub\Makers\PackageMaker;

class ComponentsTest extends TestCaseEpub
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
        $container = new ContainerMaker(new Package($this->getFilePath('epub-basic-v3plus2/EPUB/package.opf'), 'EPUB'));
        $xml = $container->makeContent();

        $this->assertXmlStringEqualsXmlFile($this->getFilePath('epub-basic-v3plus2/META-INF/container.xml'), $xml);
    }

    public function testNavegation(): void
    {
        $epub = new Epub('test');
        $this->assertInstanceOf(NavegationMaker::class, $epub->nav());

        $epub->nav()->createMainMenu('chapter1.xhtml', 'Chapter 1');
        $mainMenu2 = $epub->nav()->createMainMenu('chapter2.xhtml', 'Chapter 2');

        $mainMenu2->createSubMenu('chapter3.xhtml', 'Chapter 3');
        $mainMenu2->createSubMenu('chapter4.xhtml', 'Chapter 4');

        $subMenu5 = $mainMenu2->createSubMenu('chapter5.xhtml', 'Chapter 5');
        $subMenu5->createSubMenu('chapter6.xhtml', 'Chapter 6');

        $xhtml = $epub->nav()->makeContent();

        $filePath = FileManager::getInstance()->saveFile('nav.xhtml', $xhtml, 'EPUB');
        $this->assertStringEqualsFile($filePath, $xhtml);

        $this->assertXmlStringEqualsXmlString($this->navXhtmlContent, $xhtml);
    }

    public function testFile(): void
    {
        $imagePath = $this->getFilePath('covers/sample-cover.jpg');
        $image = new Image($imagePath, 'EPUB/images');
        $this->assertInstanceOf(File::class, $image);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInstanceOf(\SplFileInfo::class, $image);
        $this->assertFileExists($image->getPath());
    }

    public function testCover(): void
    {
        $coverMaker = new CoverMaker('Test Title', 'cover.xhtml', 'EPUB/xhtml');
        $coverMaker
            ->setImage(new Image($this->getFilePath('covers/sample-cover.jpg'), 'EPUB/images'))
            ->appendCss(new Css($this->getFilePath('epub-basic-v3plus2/EPUB/css/cover.css'), 'EPUB/css'));

        $xhtml = $coverMaker->makeContent();
        $this->assertXmlStringEqualsXmlString($this->coverXhtmlContent, $xhtml);

        $filepath = $coverMaker->makeFile()->getRealPath();
        $this->assertFileExists($filepath);
        $this->assertStringEqualsFile($filepath, $xhtml);

        $filePath = FileManager::getInstance()->saveFile('cover2.xhtml', $xhtml, 'EPUB/xhtml');
        $this->assertStringEqualsFile($filePath, $xhtml);
    }

    public function testPackage(): void
    {
        $packageMaker = new PackageMaker('test Title');

        $manifestItem = ManifestItem::fromFile(new Image($this->getFilePath('covers/sample-cover.jpg'), 'EPUB/images'), 'EPUB');

        $packageMaker->createManifestItem('id-123', 'EPUB/css/cover.css', 'text/css');

        $packageMaker->appendManifestItem($manifestItem);

        $xml = $packageMaker->makeContent();
        $this->assertIsString($xml);
    }

    public function testChapter(): void
    {
        $epub = new Epub('Ladle Rat Rotten Hut');

        $chapter1 = new ChapterMaker($chapterTitle1 = 'Ladle Rat Rotten Hut', $chapterContent1 = $this->getFileContent('contents-sample/body1.html'));
        $chapter1->appendCss(new Css($this->getFilePath('epub-basic-v3plus2/EPUB/css/epub.css'), 'EPUB/css'), 'EPUB/xhtml');

        $epub->appendChapter($chapter1);

        $this->assertEquals($chapterTitle1, $chapter1->getTitle());
        $this->assertEquals($chapterContent1, $chapter1->getContent());
        $xhtml = $chapter1->makeContent();
        $this->assertStringEqualsFile($this->getFilePath('contents-sample/section1.xhtml'), $xhtml);

        $this->assertEquals(Str::slugify($chapterTitle1) . '.xhtml', $chapter1->getFilename());

        $chapter2 = new ChapterMaker($this->faker->title(), $this->getFileContent('contents-sample/body1.html'));
        $chapter3 = new ChapterMaker($this->faker->title(), $this->getFileContent('contents-sample/body1.html'));

        $epub
            ->appendChapter($chapter2)
            ->appendChapter($chapter3);

        $this->assertCount(3, $epub->getChaptersFiles());
    }
}
