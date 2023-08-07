<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use DateTime;
use Paneon\PhpToTypeScript\Annotation\Exclude;
use Paneon\PhpToTypeScript\Annotation\Type;
use Paneon\PhpToTypeScript\Annotation\TypeScriptInterface;
use Paneon\PhpToTypeScript\Annotation\VirtualProperty;

#[TypeScriptInterface]
class AttributeClass
{
    use SomeTrait;

    public string $firstName;

    public ?string $middleName;

    public string $lastName;

    public int $age;

    /** @var bool[]  */
    #[Exclude]
    protected array $excluded;

    /**
     * @var mixed[]
     */
    protected array $mixedArray;

    protected SomeClass $someClass;

    /**
     * @var SomeClass[]
     */
    protected array $classCollection;

    protected mixed $mixed;

    protected DateTime $dateTime;

    protected \DateTime $dateTime2;

    #[Type("ClassImplementingInterface1|ClassImplementingInterface2")]
    protected SomeInterface $someInterface;

    /**
     * @var SomeInterface[]
     */
    #[Type("ClassImplementingInterface1[]|ClassImplementingInterface2[]")]
    protected array $someInterfaceArray;

    /**
     * @var array<int>
     */
    protected array $psalmArrayType;

    #[VirtualProperty]
    public function hasSomeValue(): bool
    {
        return true;
    }

    #[VirtualProperty]
    public function virtualWithReturnType(): int
    {
        return 1;
    }
}
