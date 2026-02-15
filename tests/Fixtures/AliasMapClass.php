<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class AliasMapClass
{
    public \DateTimeImmutable $createdAt;

    public string $name;

    public int $count;
}
