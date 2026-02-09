<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Parser;


class PhpDocParser
{
    public const PROPERTY_TYPE_VARIABLE = 'VARIABLE';
    public const PROPERTY_TYPE_METHOD = 'METHOD';

    public function parseDocComment(
        string $phpDoc,
        $type = self::PROPERTY_TYPE_VARIABLE,
        $includeTypeNullable = false
    ): string {
        // Reusable pattern fragments
        $typeName    = '[^\s*<>|]+';          // base type name, e.g. "array", "string", "SomeClass"
        $genericArgs = '(?:<[^>]*>)?';        // optional generic args, e.g. "<string, int>"
        $arraySuffix = '(?:\[\])?';           // optional array brackets, e.g. "[]"
        $unionSep    = '(?:\|(?![\s*]))?';    // optional union separator "|" (not followed by space/*)
        $typeExpr    = "(?:{$typeName}{$genericArgs}{$arraySuffix}{$unionSep})+";

        $varRegex       = "/@var\s+(?P<var>{$typeExpr})/";
        $methodRegex    = "/@return\s+(?P<var>{$typeExpr})/";
        $typeRegex      = '/(?P<type>[^\[\]\s]+)(?P<array>\[\])?/i';
        $psalmTypeRegex = '/array\<(?P<psalmType>[^>]+)\>/i';

        if (empty($phpDoc)) {
            return 'any';
        }

        $regex = $type === self::PROPERTY_TYPE_METHOD ? $methodRegex : $varRegex;

        if (preg_match($regex, $phpDoc, $matches)) {
            $types = explode('|', $matches['var']);

            $propertyTypes = [];

            foreach ($types as $phpType) {
                $tsType = $phpType;

                if (preg_match($psalmTypeRegex, $phpType, $typeMatch)) {
                    $psalmType = trim($typeMatch['psalmType']);

                    if (str_contains($psalmType, ',')) {
                        // Key-value map: array<K, V> → Record<KeyTS, ValueTS>
                        $parts = array_map('trim', explode(',', $psalmType, 2));
                        $keyTs = $this->getTypeEquivalent($parts[0], $includeTypeNullable);
                        $valueTs = $this->getTypeEquivalent($parts[1], $includeTypeNullable);
                        $tsType = ($keyTs !== null && $valueTs !== null)
                            ? "Record<{$keyTs}, {$valueTs}>"
                            : null;
                    } else {
                        // Single type: array<V> → V[]
                        $tsType = $this->getTypeEquivalent($psalmType, $includeTypeNullable);
                        if ($tsType !== null) {
                            $tsType .= '[]';
                        }
                    }
                } else if (preg_match($typeRegex, $phpType, $typeMatch)) {
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
        // Strip leading backslash from fully qualified type names
        $phpType = ltrim($phpType, '\\');

        switch (strtolower($phpType)) {
            case 'null':
                if ($includeTypeNullable) {
                    return 'null';
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
            case 'datetimeimmutable':
                return 'string';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return $phpType;
        }
    }
}
