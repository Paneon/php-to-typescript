<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum ColorEnum
{
    case Red;
    case Green;
    case Blue;
}
