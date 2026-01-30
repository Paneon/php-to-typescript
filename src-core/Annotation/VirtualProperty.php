<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Annotation;

/**
 * Class TypeScriptInterface
 *
 * @Annotation
 * @Target("METHOD")
 */
#[\Attribute(flags: \Attribute::TARGET_METHOD)]
class VirtualProperty
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
