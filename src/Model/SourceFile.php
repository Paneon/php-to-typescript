<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Model;

/**
 * Represents a PHP source file to be converted to TypeScript.
 * Used to calculate import paths when generating TypeScript with exports.
 */
class SourceFile
{
    public function __construct(
        public readonly string $className,
        public readonly string $sourceFile,
        public readonly string $targetDirectory,
    ) {}

    /**
     * Returns the TypeScript output filename (without path).
     */
    public function getOutputFileName(string $prefix = '', string $suffix = '', bool $useType = false): string
    {
        $extension = $useType ? '.ts' : '.d.ts';
        return $prefix . $this->getShortName() . $suffix . $extension;
    }

    /**
     * Returns the full output path.
     */
    public function getOutputPath(string $prefix = '', string $suffix = '', bool $useType = false): string
    {
        return rtrim($this->targetDirectory, '/') . '/' . $this->getOutputFileName($prefix, $suffix, $useType);
    }

    /**
     * Returns the short class/enum name without namespace.
     */
    public function getShortName(): string
    {
        $parts = explode('\\', $this->className);
        return end($parts);
    }
}
