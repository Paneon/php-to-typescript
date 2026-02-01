<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;

class ParserServiceBackslashTest extends AbstractTestCase
{
    public function testStripLeadingBackslashFromTypes(): void
    {
        $fixture = __DIR__ . '/../Fixtures/BackslashTypeClass.php';
        $content = $this->parserService->getInterfaceContent($fixture);

        // PHPDoc annotations: Should convert \DateTimeImmutable to string (not preserve backslash)
        $this->assertStringContainsString('createdAt: string;', $content);
        $this->assertStringNotContainsString('\\DateTimeImmutable', $content);

        // PHPDoc annotations: Should convert \DateTime to string
        $this->assertStringContainsString('updatedAt: string;', $content);
        $this->assertStringNotContainsString('\\DateTime', $content);

        // PHP 8 native type hints: Should also strip backslashes
        $this->assertStringContainsString('nativeDateTime: string;', $content);
        $this->assertStringContainsString('nativeDateTimeImmutable: string;', $content);
        $this->assertStringNotContainsString('\\', $content);
    }
}
