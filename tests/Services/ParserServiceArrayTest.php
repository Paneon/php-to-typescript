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

    public function testGenericKeyValueMap()
    {
        $content = $this->loadGenericArrayFixture();

        $this->assertStringContainsString('skuMap: Record<string, number>;', $content);
    }

    public function testNullableGenericKeyValueMapWithNullableEnabled()
    {
        $this->parserService->setIncludeTypeNullable(true);
        $content = $this->loadGenericArrayFixture();

        $this->assertStringContainsString('nullableSkuMap: Record<string, number>|null;', $content);
    }

    public function testNullableGenericKeyValueMapWithNullableDisabled()
    {
        $content = $this->loadGenericArrayFixture();

        $this->assertStringContainsString('nullableSkuMap: Record<string, number>;', $content);
    }

    public function testSimpleGenericArrayRemainsUnchanged()
    {
        $content = $this->loadGenericArrayFixture();

        $this->assertStringContainsString('simpleGenericArray: number[];', $content);
    }

    public function testGenericKeyValueMapWithObjectValue()
    {
        $content = $this->loadGenericArrayFixture();

        $this->assertStringContainsString('objectMap: Record<string, SomeClass>;', $content);
    }

    private function loadGenericArrayFixture(): ?string
    {
        return $this->parserService->getInterfaceContent(
            __DIR__ . '/../Fixtures/GenericArrayClass.php'
        );
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
