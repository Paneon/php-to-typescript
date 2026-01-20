<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Attribute;

use Attribute;

/**
 * Marks a class or enum for TypeScript generation.
 *
 * For classes: generates a TypeScript interface (or type with useType option)
 * For enums: generates a TypeScript enum (or string literal union with asUnion option)
 */
#[Attribute(flags: Attribute::TARGET_CLASS)]
class TypeScript
{
    public function __construct(
        /**
         * For enums: when true, output as string literal union type instead of enum.
         * Null uses the global setting from ParserService::setUseEnumUnionType().
         */
        public ?bool $asUnion = null
    ) {}
}
