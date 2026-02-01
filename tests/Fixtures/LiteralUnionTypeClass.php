<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class LiteralUnionTypeClass
{
    /** @var AnotherObject|false */
    public $maybeObject;

    /** @var string|true */
    public $maybeTrue;

    /** @var SomeClass|null */
    public $maybeNull;
}
