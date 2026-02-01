<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class BackslashTypeClass
{
    /** @var \DateTimeImmutable */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;

    // Test PHP 8 native type hints with backslashes
    public \DateTime $nativeDateTime;

    public \DateTimeImmutable $nativeDateTimeImmutable;
}
