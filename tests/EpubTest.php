<?php

namespace Prhost\Epub\Tests;

use Prhost\Epub\Elements\Menu;
use Prhost\Epub\Epub;
use Prhost\Epub\FileManager;
use Prhost\Epub\Files\Css;

class EpubTest extends TestCaseEpub
{
    public function testMakeSimpleEpub(): void
    {
        $epubTitle = 'Simple Epub Test';

        $epub = new Epub($epubTitle);

        $dirName = $this->faker->slug('3');

        FileManager::getInstance()->setRootDirName($dirName);

        $epub->cover()
            ->setImage($this->getFilePath('covers/sample-cover.jpg'))
            ->appendCss(new Css($this->getFilePath('epub-basic-v3plus2/EPUB/css/cover.css')));

        $epub
            ->addChapter('Chapter 1', $this->getFileContent('contents-sample/body1.html'), 'chapter1.xhtml')
            ->addChapter('Chapter 2', $this->getFileContent('contents-sample/body2.html'), 'chapter2.xhtml')
            ->nav()
            ->appendMainMenu(new Menu('xhtml/chapter1.xhtml', 'Chapter 1'))
            ->appendMainMenu(new Menu('xhtml/chapter2.xhtml', 'Chapter 2'));


        $epub->saveRaw(__DIR__ . '/files/output/' . $dirName);

        $epub->save(__DIR__ . '/files/output', $dirName . '.epub');

        $this->assertIsString('');
    }
}
