<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class GenericArrayClass
{
    /**
     * @var array<string, int>
     */
    protected $skuMap;

    /**
     * @var array<string, int>|null
     */
    protected $nullableSkuMap;

    /**
     * @var array<int>
     */
    protected $simpleGenericArray;

    /**
     * @var array<string, SomeClass>
     */
    protected $objectMap;
}
