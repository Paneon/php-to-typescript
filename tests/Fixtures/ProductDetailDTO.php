<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
final readonly class ProductDetailDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $price,
    ) {}
}
