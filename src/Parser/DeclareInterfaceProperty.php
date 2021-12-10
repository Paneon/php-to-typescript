<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Parser;

class DeclareInterfaceProperty
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    protected $indentSize = 2;

    protected $indent = '  ';

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $suffix = '';


    public function __construct($name, $type = "any")
    {
        $this->name = $name;
        $this->type = $type;
    }

	public function getType()
    {
        $types = explode('|', $this->type);
        $resultTypes = [];

        foreach ($types as $type) {
            if (DeclareInterfaceProperty::isPrimitive($type)) {
                $resultTypes[] = $type;
                continue;
            }

            $resultTypes[] = $this->prefix . $type . $this->suffix;
        }

        return implode('|', $resultTypes);
    }

    public static function isPrimitive($type)
    {
        $type = strtolower($type);
        $type = str_replace('[]', '', $type);

        switch (strtolower($type)) {
            case 'null':
            case 'array':
            case 'any':
            case 'number':
            case 'string':
            case 'boolean':
            case 'object':
            case 'function':
                return true;
        }
        return false;
    }

    public function __toString()
    {
        return "{$this->indent}{$this->name}: {$this->getType()};";
    }

    public function setIndentSize(int $indentSize): DeclareInterfaceProperty
    {
        $this->indentSize = $indentSize;

        $this->indent = str_pad('', $indentSize);

        return $this;
    }

    public function setPrefix(string $prefix): DeclareInterfaceProperty
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function setSuffix(string $suffix): DeclareInterfaceProperty
    {
        $this->suffix = $suffix;
        return $this;
    }
}
