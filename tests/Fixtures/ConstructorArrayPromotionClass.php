<?php

declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
class ConstructorArrayPromotionClass
{
    public function __construct(
        public array $jobs,
    ) {
    }
}
