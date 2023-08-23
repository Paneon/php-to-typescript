<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use DateTime;
use FixtureInterface;
use Paneon\PhpToTypeScript\Annotation\Exclude;
use Paneon\PhpToTypeScript\Annotation\Type;
use Paneon\PhpToTypeScript\Annotation\TypeScriptInterface;
use Paneon\PhpToTypeScript\Annotation\VirtualProperty;

#[TypeScriptInterface]
class AttributeTypeFixture
{
    #[Exclude]
    protected array $excluded;

    #[Type("ClassImplementingInterface1|ClassImplementingInterface2")]
    protected FixtureInterface $someInterface;

    /**
     * @var FixtureInterface[]
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
