<?php

namespace Paneon\PhpToTypeScript\Tests\Fixtures;

use Paneon\PhpToTypeScript\Attribute\TypeScript;

#[TypeScript]
final readonly class CartResponseDTO
{
    public function __construct(
        public int $count,
        public string $total,
        /** @var ProductDetailDTO[] */
        public array $items,
    ) {}
}
