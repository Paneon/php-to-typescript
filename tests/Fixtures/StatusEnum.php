<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum StatusEnum: int
{
    case Pending = 0;
    case Active = 1;
    case Completed = 2;
    case Cancelled = 3;
}
