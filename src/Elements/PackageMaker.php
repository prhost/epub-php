<?php
declare(strict_types=1);

namespace Prhost\Epub3\Elements;

use Ramsey\Uuid\Uuid;

class PackageMaker
{
    protected function generateVersion(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function generateXml(): string
    {
        return '';
    }
}