<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class User
{
    public string $name;
    public string $email;
    public ?Address $address;
    public SampleEnum $status;
}
