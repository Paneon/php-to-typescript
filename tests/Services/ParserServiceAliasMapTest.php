<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceAliasMapTest extends AbstractTestCase
{
    public function testAliasMapOverridesBuiltInMapping(): void
    {
        $this->parserService->setAliasMap([
            'DateTimeImmutable' => 'number',
        ]);

        $fixture = __DIR__ . '/../Fixtures/AliasMapClass.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        // DateTimeImmutable should now map to number instead of string
        $this->assertStringContainsString('createdAt: number;', $content);
    }

    public function testUnmappedTypesStillWorkNormally(): void
    {
        $this->parserService->setAliasMap([
            'DateTimeImmutable' => 'number',
        ]);

        $fixture = __DIR__ . '/../Fixtures/AliasMapClass.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        // Built-in mappings should still work for unmapped types
        $this->assertStringContainsString('name: string;', $content);
        $this->assertStringContainsString('count: number;', $content);
    }

    public function testEmptyAliasMapPreservesDefaults(): void
    {
        $this->parserService->setAliasMap([]);

        $fixture = __DIR__ . '/../Fixtures/AliasMapClass.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        // Default mapping: DateTimeImmutable -> string
        $this->assertStringContainsString('createdAt: string;', $content);
    }
}
