<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Parser;


class PhpDocParser
{
    public const PROPERTY_TYPE_VARIABLE = 'VARIABLE';
    public const PROPERTY_TYPE_METHOD = 'METHOD';

    public function parseDocComment(string $phpDoc, $type = self::PROPERTY_TYPE_VARIABLE, $includeTypeNullable = false): string
    {
        $varRegex = '/@var\s+(?P<var>[^\s*]+)?/';
        $methodRegex = '/@return\s+(?P<var>[^\s*]+)?/';
        $typeRegex = '/(?P<type>[^\[\]\s]+)(?P<array>\[\])?/i';

        if (empty($phpDoc)) {
            return 'any';
        }

        $regex = $type === self::PROPERTY_TYPE_METHOD ? $methodRegex : $varRegex;

        if (preg_match($regex, $phpDoc, $matches)) {
            $types = explode('|', $matches['var']);

            $propertyTypes = [];

            foreach ($types as $phpType) {
                $tsType = $phpType;

                if (preg_match($typeRegex, $phpType, $typeMatch)) {
                    $tsType = $this->getTypeEquivalent($typeMatch['type'], $includeTypeNullable);
                    if ($tsType === null) {
                        continue;
                    }

                    if (!empty($typeMatch['array'])) {
                        $tsType .= '[]';
                    }

                }

                $propertyTypes[] = $tsType;
            }

            return implode('|', $propertyTypes);
        }

        return 'any';
    }

    public function getTypeEquivalent(string $phpType, $includeTypeNullable = false): ?string
    {
        switch (strtolower($phpType)) {
            case 'null':
                if ($includeTypeNullable) {
                    return 'null';
                    break;
                }
                return null;
            case 'array':
                return 'Array<any>';
            case 'mixed':
                return 'any';
            case 'int':
            case 'integer':
            case 'float':
                return 'number';
            case 'string':
            case 'datetime':
            case '\datetime':
                return 'string';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return $phpType;
        }
    }
}
