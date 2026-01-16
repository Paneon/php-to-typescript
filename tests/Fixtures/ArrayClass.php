<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use DateTime;
use Paneon\PhpToTypeScript\Annotation as PTS;

#[PTS\TypeScriptInterface]
class ArrayClass
{
    #[PTS\Exclude]
    protected $excluded;

    /**
     * @var mixed[]
     */
    protected $mixedArray;

    /**
     * @var SomeClass[]
     */
    protected $classCollection;

    /**
     * @var mixed
     */
    protected $mixed;

    #[PTS\Type('ClassImplementingInterface1[]|ClassImplementingInterface2[]')]
    protected $someInterfaceArray;

    /**
     * This syntax is not correct PHPDoc actually, but anyway used sometimes.
     *
     * @var array<int>
     */
    protected $psalmArrayType;

    #[PTS\VirtualProperty]
    public function hasSomeValue()
    {
        return [true];
    }

    #[PTS\VirtualProperty]
    public function virtualWithReturnType(): int
    {
        return 1;
    }
}
