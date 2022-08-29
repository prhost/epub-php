<?php
declare(strict_types=1);

namespace Prhost\Epub3;

use Ramsey\Uuid\Uuid;

class Package
{
    protected ?string $version;

    protected function generateVersion(): string
    {
        return Uuid::uuid4()->toString();
    }
}