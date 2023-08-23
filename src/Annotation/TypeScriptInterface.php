<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Annotation;

use Attribute;

/**
 * Class TypeScriptInterface
 *
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(flags: Attribute::TARGET_CLASS)]
class TypeScriptInterface
{
    public $if = true;
}
