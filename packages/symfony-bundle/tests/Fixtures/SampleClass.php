<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class SampleClass
{
    public string $name;
    public int $age;
    public ?string $email;
}
