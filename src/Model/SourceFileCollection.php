<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScript\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection of SourceFile objects.
 * Used to resolve import paths between TypeScript files.
 *
 * @implements IteratorAggregate<int, SourceFile>
 */
class SourceFileCollection implements IteratorAggregate, Countable
{
    /** @var array<string, SourceFile> Indexed by short class name */
    private array $sourceFiles = [];

    public function add(SourceFile $sourceFile): self
    {
        $this->sourceFiles[$sourceFile->getShortName()] = $sourceFile;
        return $this;
    }

    public function addFromArray(string $className, string $sourceFile, string $targetDirectory): self
    {
        return $this->add(new SourceFile($className, $sourceFile, $targetDirectory));
    }

    /**
     * Find a SourceFile by its short class/enum name.
     */
    public function findByName(string $shortName): ?SourceFile
    {
        return $this->sourceFiles[$shortName] ?? null;
    }

    /**
     * Check if a type exists in the collection.
     */
    public function has(string $shortName): bool
    {
        return isset($this->sourceFiles[$shortName]);
    }

    /**
     * Calculate the relative import path from one file to another.
     *
     * @param string $fromTargetDir The target directory of the importing file
     * @param string $toTypeName The short name of the type to import
     * @param string $prefix Prefix for generated type names
     * @param string $suffix Suffix for generated type names
     * @return string|null The relative import path, or null if type not found
     */
    public function getImportPath(
        string $fromTargetDir,
        string $toTypeName,
        string $prefix = '',
        string $suffix = ''
    ): ?string {
        $sourceFile = $this->findByName($toTypeName);
        if ($sourceFile === null) {
            return null;
        }

        $fromDir = realpath($fromTargetDir) ?: $fromTargetDir;
        $toDir = realpath($sourceFile->targetDirectory) ?: $sourceFile->targetDirectory;

        // Calculate relative path
        $relativePath = $this->calculateRelativePath($fromDir, $toDir);

        // Build the import path (without extension for TypeScript imports)
        $fileName = $prefix . $toTypeName . $suffix;

        if ($relativePath === '') {
            return './' . $fileName;
        }

        return $relativePath . '/' . $fileName;
    }

    /**
     * Calculate relative path between two directories.
     */
    private function calculateRelativePath(string $from, string $to): string
    {
        $from = rtrim($from, '/');
        $to = rtrim($to, '/');

        if ($from === $to) {
            return '.';
        }

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Find common base
        $commonLength = 0;
        $maxLength = min(count($fromParts), count($toParts));

        for ($i = 0; $i < $maxLength; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                break;
            }
            $commonLength++;
        }

        // Build relative path
        $upCount = count($fromParts) - $commonLength;
        $downParts = array_slice($toParts, $commonLength);

        $relativeParts = [];

        if ($upCount === 0) {
            $relativeParts[] = '.';
        } else {
            for ($i = 0; $i < $upCount; $i++) {
                $relativeParts[] = '..';
            }
        }

        $relativeParts = array_merge($relativeParts, $downParts);

        return implode('/', $relativeParts);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->sourceFiles));
    }

    public function count(): int
    {
        return count($this->sourceFiles);
    }

    /**
     * @return string[] List of all short names in the collection
     */
    public function getNames(): array
    {
        return array_keys($this->sourceFiles);
    }
}
