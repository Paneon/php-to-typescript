<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Fixtures\Nested;

use Paneon\PhpToTypeScript\Attribute\TypeScript;
use Paneon\PhpToTypeScriptBundle\Tests\Fixtures\Address;

#[TypeScript]
class Company
{
    public string $name;
    public Address $headquarters;
}
