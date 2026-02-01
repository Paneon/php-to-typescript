<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
enum SampleEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
