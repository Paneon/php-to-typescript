<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use DateTime;
use Paneon\PhpToTypeScript\Annotation as PTS;
use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class Person
{
    use SomeTrait;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $middleName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var int
     */
    public $age;

    #[PTS\Exclude]
    /**
     * @var bool[]
     */
    protected $excluded;

    /**
     * @var mixed[]
     */
    protected $mixedArray;

    /**
     * @var SomeClass
     */
    protected $someClass;

    /**
     * @var SomeClass[]
     */
    protected $classCollection;

    /**
     * @var mixed
     */
    protected $mixed;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var \DateTime
     */
    protected $dateTime2;

    #[PTS\Type('ClassImplementingInterface1|ClassImplementingInterface2')]
    /**
     * @var SomeInterface
     */
    protected $someInterface;

    #[PTS\Type('ClassImplementingInterface1[]|ClassImplementingInterface2[]')]
    /**
     * @var SomeInterface[]
     */
    protected $someInterfaceArray;

    /**
     * This syntax is not correct PHPDoc actually, but anyway used sometimes.
     *
     * @var array<int>
     */
    protected $psalmArrayType;

    #[PTS\VirtualProperty]
    /**
     * @return bool
     */
    public function hasSomeValue()
    {
        return true;
    }

    #[PTS\VirtualProperty]
    public function virtualWithReturnType(): int
    {
        return 1;
    }
}
