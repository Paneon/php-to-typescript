<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Attribute;

/**
 * Marks a class for TypeScript interface generation.
 *
 * @deprecated Use \Paneon\PhpToTypeScript\Attribute\TypeScript instead
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS)]
class TypeScriptInterface
{
}
