<?php

namespace Paneon\PhpToTypeScript\Tests\Services;

use Paneon\PhpToTypeScript\Tests\AbstractTestCase;
use ReflectionException;

class ParserServiceArrayTest extends AbstractTestCase
{
    public function testConvertsMixedToArray()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('mixed: any;', $content);
        $this->assertStringContainsString('mixedArray: any[];', $content);
    }

    public function testRespectsTypeScriptTypeAnnotationForArrays()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('someInterfaceArray: ClassImplementingInterface1[]|ClassImplementingInterface2[];', $content);
    }

    public function testPhpDocArraySyntax()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('classCollection: SomeClass[];', $content);
    }

    public function testPsalmArraySyntax()
    {
        $content = $this->loadFixture();

        $this->assertStringContainsString('psalmArrayType: number[];', $content);
    }

    private function loadFixture(): ?string
    {
        $fixture = $this->getDefaultFixtureFile();
        return $this->parserService->getInterfaceContent($fixture);
    }

    private function getDefaultFixtureFile(): string
    {
        return __DIR__ . '/../Fixtures/ArrayClass.php';
    }
}
