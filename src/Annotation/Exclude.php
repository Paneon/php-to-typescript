<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Annotation;

use Attribute;

/**
 * Class TypeScriptInterface
 *
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Exclude
{
    protected array $type;

    public function __construct($custom = null) {
        $this->type = $custom;
    }

    public function getType(): string
    {
        if(!$this->type || empty($this->type['value'])){
            return 'any';
        }

        return $this->type['value'];
    }
}
