<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Annotation;

/**
 * Class TypeScriptInterface
 *
 * @Annotation
 * @Target("PROPERTY")
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
class Type
{
    protected array $type;

    public function __construct(mixed $custom = null) {
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
