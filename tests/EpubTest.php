<?php

namespace Prhost\Epub3\Tests;

use Prhost\Epub3\Elements\Menu;
use Prhost\Epub3\Epub;
use Prhost\Epub3\FileManager;
use Prhost\Epub3\Files\Css;

class EpubTest extends TestCaseEpub
{
    public function testMakeSimpleEpub(): void
    {
        $epubTitle = 'Simple Epub Test';

        $epub = new Epub($epubTitle);

        $dirName = $this->faker->slug('3');

        FileManager::getInstance()->setRootDirName($dirName);

        $epub->cover()
            ->setImage($this->getFilePath('covers/sample-cover.jpg'), 'EPUB/images')
            ->appendCss(new Css($this->getFilePath('epub-basic-v3plus2/EPUB/css/cover.css'), 'EPUB/css'), 'EPUB/xhtml');

        $epub
            ->addChapter('Chapter 1', $this->getFileContent('contents-sample/body1.html'), 'chapter1.xhtml', 'EPUB/xhtml')
            ->addChapter('Chapter 2', $this->getFileContent('contents-sample/body2.html'), 'chapter2.xhtml', 'EPUB/xhtml')
            ->nav('EPUB/xhtml')
            ->appendMainMenu(new Menu('xhtml/chapter1.xhtml', 'Chapter 1'))
            ->appendMainMenu(new Menu('xhtml/chapter2.xhtml', 'Chapter 2'));


        $epub->saveRaw(__DIR__ . '/files/output/' . $dirName);

        $epub->save(__DIR__ . '/files/output', $dirName . '.epub');

        $this->assertIsString('');
    }
}
