<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class Address
{
    public string $street;
    public string $city;
    public string $zipCode;
    public string $country;
}
